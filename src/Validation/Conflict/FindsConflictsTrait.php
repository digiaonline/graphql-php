<?php

namespace Digia\GraphQL\Validation\Conflict;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Validation\ValidationContextInterface;
use function Digia\GraphQL\Type\getNamedType;
use function Digia\GraphQL\Util\typeFromAST;
use function Digia\GraphQL\Validation\compareArguments;
use function Digia\GraphQL\Validation\compareTypes;

/**
 * Algorithm:
 *
 * Conflicts occur when two fields exist in a query which will produce the same
 * response name, but represent differing values, thus creating a conflict.
 * The algorithm below finds all conflicts via making a series of comparisons
 * between fields. In order to compare as few fields as possible, this makes
 * a series of comparisons "within" sets of fields and "between" sets of fields.
 *
 * Given any selection set, a collection produces both a set of fields by
 * also including all inline fragments, as well as a list of fragments
 * referenced by fragment spreads.
 *
 * A) Each selection set represented in the document first compares "within" its
 * collected set of fields, finding any conflicts between every pair of
 * overlapping fields.
 * Note: This is the *only time* that a the fields "within" a set are compared
 * to each other. After this only fields "between" sets are compared.
 *
 * B) Also, if any fragment is referenced in a selection set, then a
 * comparison is made "between" the original set of fields and the
 * referenced fragment.
 *
 * C) Also, if multiple fragments are referenced, then comparisons
 * are made "between" each referenced fragment.
 *
 * D) When comparing "between" a set of fields and a referenced fragment, first
 * a comparison is made between each field in the original set of fields and
 * each field in the the referenced set of fields.
 *
 * E) Also, if any fragment is referenced in the referenced selection set,
 * then a comparison is made "between" the original set of fields and the
 * referenced fragment (recursively referring to step D).
 *
 * F) When comparing "between" two fragments, first a comparison is made between
 * each field in the first referenced set of fields and each field in the the
 * second referenced set of fields.
 *
 * G) Also, any fragments referenced by the first must be compared to the
 * second, and any fragments referenced by the second must be compared to the
 * first (recursively referring to step F).
 *
 * H) When comparing two fields, if both have selection sets, then a comparison
 * is made "between" both selection sets, first comparing the set of fields in
 * the first selection set with the set of fields in the second.
 *
 * I) Also, if any fragment is referenced in either selection set, then a
 * comparison is made "between" the other set of fields and the
 * referenced fragment.
 *
 * J) Also, if two fragments are referenced in both selection sets, then a
 * comparison is made "between" the two fragments.
 *
 */
trait FindsConflictsTrait
{
    /**
     * A cache for the "field map" and list of fragment names found in any given
     * selection set. Selection sets may be asked for this information multiple
     * times, so this improves the performance of this validator.
     *
     * @var Map
     */
    protected $cachedFieldsAndFragmentNames;

    /**
     * A memoization for when two fragments are compared "between" each other for
     * conflicts. Two fragments may be compared many times, so memoizing this can
     * dramatically improve the performance of this validator.
     *
     * @var PairSet
     */
    protected $comparedFragmentPairs;

    /**
     * @return ValidationContextInterface
     */
    abstract public function getValidationContext(): ValidationContextInterface;

    /**
     * @param Map                     $cachedFieldsAndFragmentNames
     * @param PairSet                 $comparedFragmentPairs
     * @param SelectionSetNode        $selectionSet
     * @param NamedTypeInterface|null $parentType
     * @return array|Conflict[]
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    protected function findConflictsWithinSelectionSet(
        Map $cachedFieldsAndFragmentNames,
        PairSet $comparedFragmentPairs,
        SelectionSetNode $selectionSet,
        ?NamedTypeInterface $parentType = null
    ): array {
        $this->cachedFieldsAndFragmentNames = $cachedFieldsAndFragmentNames;
        $this->comparedFragmentPairs        = $comparedFragmentPairs;

        $context = $this->getFieldsAndFragmentNames($selectionSet, $parentType);

        // (A) Find find all conflicts "within" the fields of this selection set.
        // Note: this is the *only place* `collectConflictsWithin` is called.
        $this->collectConflictsWithin($context);

        $fieldMap      = $context->getFieldMap();
        $fragmentNames = $context->getFragmentNames();

        // (B) Then collect conflicts between these fields and those represented by
        // each spread fragment name found.
        if (!empty($fragmentNames)) {
            $fragmentNamesCount = \count($fragmentNames);
            $comparedFragments  = [];

            /** @noinspection ForeachInvariantsInspection */
            for ($i = 0; $i < $fragmentNamesCount; $i++) {
                $this->collectConflictsBetweenFieldsAndFragment(
                    $context,
                    $comparedFragments,
                    $fieldMap,
                    $fragmentNames[$i],
                    false/* $areMutuallyExclusive */
                );

                // (C) Then compare this fragment with all other fragments found in this
                // selection set to collect conflicts between fragments spread together.
                // This compares each item in the list of fragment names to every other
                // item in that same list (except for itself).
                for ($j = $i + 1; $j < $fragmentNamesCount; $j++) {
                    $this->collectConflictsBetweenFragments(
                        $context,
                        $fragmentNames[$i],
                        $fragmentNames[$j],
                        false/* $areMutuallyExclusive */
                    );
                }
            }
        }

        return $context->getConflicts();
    }

    /**
     * Collect all conflicts found between a set of fields and a fragment reference
     * including via spreading in any nested fragments.
     *
     * @param ComparisonContext $context
     * @param array             $comparedFragments
     * @param array             $fieldMap
     * @param string            $fragmentName
     * @param bool              $areMutuallyExclusive
     * @throws InvalidTypeException
     */
    protected function collectConflictsBetweenFieldsAndFragment(
        ComparisonContext $context,
        array &$comparedFragments,
        array $fieldMap,
        string $fragmentName,
        bool $areMutuallyExclusive
    ): void {
        // Memoize so a fragment is not compared for conflicts more than once.
        if (isset($comparedFragments[$fragmentName])) {
            return;
        }

        $comparedFragments[$fragmentName] = true;

        $fragment = $this->getValidationContext()->getFragment($fragmentName);

        if (null === $fragment) {
            return;
        }

        $contextB = $this->getReferencedFieldsAndFragmentNames($fragment);

        $fieldMapB = $contextB->getFieldMap();

        // Do not compare a fragment's fieldMap to itself.
        if ($fieldMap == $fieldMapB) {
            return;
        }

        // (D) First collect any conflicts between the provided collection of fields
        // and the collection of fields represented by the given fragment.
        $this->collectConflictsBetween(
            $context,
            $fieldMap,
            $fieldMapB,
            $areMutuallyExclusive
        );

        $fragmentNamesB = $contextB->getFragmentNames();

        // (E) Then collect any conflicts between the provided collection of fields
        // and any fragment names found in the given fragment.
        if (!empty($fragmentNamesB)) {
            $fragmentNamesBCount = \count($fragmentNamesB);

            /** @noinspection ForeachInvariantsInspection */
            for ($i = 0; $i < $fragmentNamesBCount; $i++) {
                $this->collectConflictsBetweenFieldsAndFragment(
                    $context,
                    $comparedFragments,
                    $fieldMap,
                    $fragmentNamesB[$i],
                    $areMutuallyExclusive
                );
            }
        }
    }

    /**
     * Collect all conflicts found between two fragments, including via spreading in
     * any nested fragments.
     *
     * @param ComparisonContext $context
     * @param string            $fragmentNameA
     * @param string            $fragmentNameB
     * @param bool              $areMutuallyExclusive
     * @throws InvalidTypeException
     */
    protected function collectConflictsBetweenFragments(
        ComparisonContext $context,
        string $fragmentNameA,
        string $fragmentNameB,
        bool $areMutuallyExclusive
    ): void {
        // No need to compare a fragment to itself.
        if ($fragmentNameA === $fragmentNameB) {
            return;
        }

        // Memoize so two fragments are not compared for conflicts more than once.
        if ($this->comparedFragmentPairs->has($fragmentNameA, $fragmentNameB, $areMutuallyExclusive)) {
            return;
        }

        $this->comparedFragmentPairs->add($fragmentNameA, $fragmentNameB, $areMutuallyExclusive);

        $fragmentA = $this->getValidationContext()->getFragment($fragmentNameA);
        $fragmentB = $this->getValidationContext()->getFragment($fragmentNameB);

        if (null === $fragmentA || null === $fragmentB) {
            return;
        }

        $contextA = $this->getReferencedFieldsAndFragmentNames($fragmentA);
        $contextB = $this->getReferencedFieldsAndFragmentNames($fragmentB);

        // (F) First, collect all conflicts between these two collections of fields
        // (not including any nested fragments).
        $this->collectConflictsBetween(
            $context,
            $contextA->getFieldMap(),
            $contextB->getFieldMap(),
            $areMutuallyExclusive
        );

        $fragmentNamesB = $contextB->getFragmentNames();

        // (G) Then collect conflicts between the first fragment and any nested
        // fragments spread in the second fragment.
        if (!empty($fragmentNamesB)) {
            $fragmentNamesBCount = \count($fragmentNamesB);

            /** @noinspection ForeachInvariantsInspection */
            for ($j = 0; $j < $fragmentNamesBCount; $j++) {
                $this->collectConflictsBetweenFragments(
                    $context,
                    $fragmentNameA,
                    $fragmentNamesB[$j],
                    $areMutuallyExclusive
                );
            }
        }

        $fragmentNamesA = $contextA->getFragmentNames();

        // (G) Then collect conflicts between the second fragment and any nested
        // fragments spread in the first fragment.
        if (!empty($fragmentNamesA)) {
            $fragmentNamesACount = \count($fragmentNamesA);

            /** @noinspection ForeachInvariantsInspection */
            for ($i = 0; $i < $fragmentNamesACount; $i++) {
                $this->collectConflictsBetweenFragments(
                    $context,
                    $fragmentNamesA[$i],
                    $fragmentNameB,
                    $areMutuallyExclusive
                );
            }
        }
    }

    /**
     * Find all conflicts found between two selection sets, including those found
     * via spreading in fragments. Called when determining if conflicts exist
     * between the sub-fields of two overlapping fields.
     *
     * @param NamedTypeInterface|null $parentTypeA
     * @param SelectionSetNode        $selectionSetA
     * @param NamedTypeInterface|null $parentTypeB
     * @param SelectionSetNode        $selectionSetB
     * @param bool                    $areMutuallyExclusive
     * @return Conflict[]
     * @throws InvalidTypeException
     */
    protected function findConflictsBetweenSubSelectionSets(
        ?NamedTypeInterface $parentTypeA,
        SelectionSetNode $selectionSetA,
        ?NamedTypeInterface $parentTypeB,
        SelectionSetNode $selectionSetB,
        bool $areMutuallyExclusive
    ): array {
        $context = new ComparisonContext();

        $contextA = $this->getFieldsAndFragmentNames($selectionSetA, $parentTypeA);
        $contextB = $this->getFieldsAndFragmentNames($selectionSetB, $parentTypeB);

        $fieldMapA = $contextA->getFieldMap();
        $fieldMapB = $contextB->getFieldMap();

        $fragmentNamesA = $contextA->getFragmentNames();
        $fragmentNamesB = $contextB->getFragmentNames();

        $fragmentNamesACount = \count($fragmentNamesA);
        $fragmentNamesBCount = \count($fragmentNamesB);

        // (H) First, collect all conflicts between these two collections of field.
        $this->collectConflictsBetween(
            $context,
            $fieldMapA,
            $fieldMapB,
            $areMutuallyExclusive
        );

        // (I) Then collect conflicts between the first collection of fields and
        // those referenced by each fragment name associated with the second.
        if (!empty($fragmentNamesB)) {
            $comparedFragments = [];

            /** @noinspection ForeachInvariantsInspection */
            for ($j = 0; $j < $fragmentNamesBCount; $j++) {
                $this->collectConflictsBetweenFieldsAndFragment(
                    $context,
                    $comparedFragments,
                    $fieldMapA,
                    $fragmentNamesB[$j],
                    $areMutuallyExclusive
                );
            }
        }

        // (I) Then collect conflicts between the second collection of fields and
        // those referenced by each fragment name associated with the first.
        if (!empty($fragmentNamesA)) {
            $comparedFragments = [];

            /** @noinspection ForeachInvariantsInspection */
            for ($i = 0; $i < $fragmentNamesACount; $i++) {
                $this->collectConflictsBetweenFieldsAndFragment(
                    $context,
                    $comparedFragments,
                    $fieldMapB,
                    $fragmentNamesA[$i],
                    $areMutuallyExclusive
                );
            }
        }

        /** @noinspection ForeachInvariantsInspection */
        for ($i = 0; $i < $fragmentNamesACount; $i++) {
            /** @noinspection ForeachInvariantsInspection */
            for ($j = 0; $j < $fragmentNamesBCount; $j++) {
                $this->collectConflictsBetweenFragments(
                    $context,
                    $fragmentNamesA[$i],
                    $fragmentNamesB[$j],
                    $areMutuallyExclusive
                );
            }
        }

        return $context->getConflicts();
    }

    /**
     * Collect all Conflicts "within" one collection of fields.
     *
     * @param ComparisonContext $context
     * @throws InvalidTypeException
     */
    protected function collectConflictsWithin(ComparisonContext $context): void
    {
        // A field map is a keyed collection, where each key represents a response
        // name and the value at that key is a list of all fields which provide that
        // response name. For every response name, if there are multiple fields, they
        // must be compared to find a potential conflict.
        foreach ($context->getFieldMap() as $responseName => $fields) {
            $fieldsCount = \count($fields);

            // This compares every field in the list to every other field in this list
            // (except to itself). If the list only has one item, nothing needs to
            // be compared.
            if ($fieldsCount > 1) {
                /** @noinspection ForeachInvariantsInspection */
                for ($i = 0; $i < $fieldsCount; $i++) {
                    for ($j = $i + 1; $j < $fieldsCount; $j++) {
                        $conflict = $this->findConflict(
                            $responseName,
                            $fields[$i],
                            $fields[$j],
                            // within one collection is never mutually exclusive
                            false/* $areMutuallyExclusive */
                        );

                        if (null !== $conflict) {
                            $context->reportConflict($conflict);
                        }
                    }
                }
            }
        }
    }

    /**
     * Collect all Conflicts between two collections of fields. This is similar to,
     * but different from the `collectConflictsWithin` function above. This check
     * assumes that `collectConflictsWithin` has already been called on each
     * provided collection of fields. This is true because this validator traverses
     * each individual selection set.
     *
     * @param ComparisonContext $context
     * @param array             $fieldMapA
     * @param array             $fieldMapB
     * @param bool              $parentFieldsAreMutuallyExclusive
     * @throws InvalidTypeException
     */
    protected function collectConflictsBetween(
        ComparisonContext $context,
        array $fieldMapA,
        array $fieldMapB,
        bool $parentFieldsAreMutuallyExclusive
    ): void {
        // A field map is a keyed collection, where each key represents a response
        // name and the value at that key is a list of all fields which provide that
        // response name. For any response name which appears in both provided field
        // maps, each field from the first field map must be compared to every field
        // in the second field map to find potential conflicts.
        foreach ($fieldMapA as $responseName => $fieldsA) {
            $fieldsB = $fieldMapB[$responseName] ?? null;

            if (null !== $fieldsB) {
                $fieldsACount = \count($fieldsA);
                $fieldsBCount = \count($fieldsB);
                /** @noinspection ForeachInvariantsInspection */
                for ($i = 0; $i < $fieldsACount; $i++) {
                    /** @noinspection ForeachInvariantsInspection */
                    for ($j = 0; $j < $fieldsBCount; $j++) {
                        $conflict = $this->findConflict(
                            $responseName,
                            $fieldsA[$i],
                            $fieldsB[$j],
                            $parentFieldsAreMutuallyExclusive
                        );

                        if (null !== $conflict) {
                            $context->reportConflict($conflict);
                        }
                    }
                }
            }
        }
    }

    /**
     * Determines if there is a conflict between two particular fields, including
     * comparing their sub-fields.
     *
     * @param string       $responseName
     * @param FieldContext $fieldA
     * @param FieldContext $fieldB
     * @param bool         $parentFieldsAreMutuallyExclusive
     * @return Conflict|null
     * @throws InvalidTypeException
     */
    protected function findConflict(
        string $responseName,
        FieldContext $fieldA,
        FieldContext $fieldB,
        bool $parentFieldsAreMutuallyExclusive
    ): ?Conflict {
        $parentTypeA = $fieldA->getParentType();
        $parentTypeB = $fieldB->getParentType();

        // If it is known that two fields could not possibly apply at the same
        // time, due to the parent types, then it is safe to permit them to diverge
        // in aliased field or arguments used as they will not present any ambiguity
        // by differing.
        // It is known that two parent types could never overlap if they are
        // different Object types. Interface or Union types might overlap - if not
        // in the current state of the schema, then perhaps in some future version,
        // thus may not safely diverge.
        $areMutuallyExclusive = $parentFieldsAreMutuallyExclusive
            || ($parentTypeA !== $parentTypeB
                && $parentTypeA instanceof ObjectType
                && $parentTypeB instanceof ObjectType);

        $nodeA = $fieldA->getNode();
        $nodeB = $fieldB->getNode();

        $definitionA = $fieldA->getDefinition();
        $definitionB = $fieldB->getDefinition();

        if (!$areMutuallyExclusive) {
            // Two aliases must refer to the same field.
            $nameA = $nodeA->getNameValue();
            $nameB = $nodeB->getNameValue();

            if ($nameA !== $nameB) {
                return new Conflict(
                    $responseName,
                    sprintf('%s and %s are different fields', $nameA, $nameB),
                    [$nodeA],
                    [$nodeB]
                );
            }

            // Two field calls must have the same arguments.
            if (!compareArguments($nodeA->getArguments(), $nodeB->getArguments())) {
                return new Conflict(
                    $responseName,
                    'they have differing arguments',
                    [$nodeA],
                    [$nodeB]
                );
            }
        }

        // The return type for each field.
        $typeA = null !== $definitionA ? $definitionA->getType() : null;
        $typeB = null !== $definitionB ? $definitionB->getType() : null;

        if (null !== $typeA && null !== $typeB && compareTypes($typeA, $typeB)) {
            return new Conflict(
                $responseName,
                sprintf('they return conflicting types %s and %s', (string)$typeA, (string)$typeB),
                [$nodeA],
                [$nodeB]
            );
        }

        // Collect and compare sub-fields. Use the same "visited fragment names" list
        // for both collections so fields in a fragment reference are never
        // compared to themselves.
        $selectionSetA = $nodeA->getSelectionSet();
        $selectionSetB = $nodeB->getSelectionSet();

        if (null !== $selectionSetA && null !== $selectionSetB) {
            $conflicts = $this->findConflictsBetweenSubSelectionSets(
                getNamedType($typeA),
                $selectionSetA,
                getNamedType($typeB),
                $selectionSetB,
                $areMutuallyExclusive
            );

            return $this->subfieldConflicts($conflicts, $responseName, $nodeA, $nodeB);
        }

        return null;
    }

    /**
     * Given a selection set, return the collection of fields (a mapping of response
     * name to field nodes and definitions) as well as a list of fragment names
     * referenced via fragment spreads.
     *
     * @param SelectionSetNode        $selectionSet
     * @param NamedTypeInterface|null $parentType
     * @return ComparisonContext
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    protected function getFieldsAndFragmentNames(
        SelectionSetNode $selectionSet,
        ?NamedTypeInterface $parentType
    ): ComparisonContext {
        $cached = $this->cachedFieldsAndFragmentNames->get($selectionSet);

        if (null === $cached) {
            $cached = new ComparisonContext();

            $this->collectFieldsAndFragmentNames($cached, $selectionSet, $parentType);

            $this->cachedFieldsAndFragmentNames->set($selectionSet, $cached);
        }

        return $cached;
    }

    /**
     * Given a reference to a fragment, return the represented collection of fields
     * as well as a list of nested fragment names referenced via fragment spreads.
     *
     * @param FragmentDefinitionNode $fragment
     * @return ComparisonContext
     * @throws InvalidTypeException
     */
    protected function getReferencedFieldsAndFragmentNames(FragmentDefinitionNode $fragment): ComparisonContext
    {
        $cached = $this->cachedFieldsAndFragmentNames->get($fragment);

        if (null !== $cached) {
            return $cached;
        }

        /** @var NamedTypeInterface $fragmentType */
        $fragmentType = typeFromAST($this->getValidationContext()->getSchema(), $fragment->getTypeCondition());

        return $this->getFieldsAndFragmentNames($fragment->getSelectionSet(), $fragmentType);
    }

    /**
     * @param ComparisonContext       $context
     * @param SelectionSetNode        $selectionSet
     * @param NamedTypeInterface|null $parentType
     * @throws InvalidTypeException
     * @throws InvariantException
     */
    protected function collectFieldsAndFragmentNames(
        ComparisonContext $context,
        SelectionSetNode $selectionSet,
        ?NamedTypeInterface $parentType
    ): void {
        foreach ($selectionSet->getSelections() as $selection) {
            if ($selection instanceof FieldNode) {
                $definition = ($parentType instanceof ObjectType || $parentType instanceof InterfaceType)
                    ? ($parentType->getFields()[$selection->getNameValue()] ?? null)
                    : null;

                $context->registerField(new FieldContext($parentType, $selection, $definition));
            } elseif ($selection instanceof FragmentSpreadNode) {
                $context->registerFragment($selection);
            } elseif ($selection instanceof InlineFragmentNode) {
                $typeCondition = $selection->getTypeCondition();

                $inlineFragmentType = null !== $typeCondition
                    ? typeFromAST($this->getValidationContext()->getSchema(), $typeCondition)
                    : $parentType;

                $this->collectFieldsAndFragmentNames($context, $selection->getSelectionSet(), $inlineFragmentType);
            }
        }
    }

    /**
     * Given a series of Conflicts which occurred between two sub-fields, generate
     * a single Conflict.
     *
     * @param array|Conflict[] $conflicts
     * @param string           $responseName
     * @param FieldNode        $nodeA
     * @param FieldNode        $nodeB
     * @return Conflict|null
     */
    protected function subfieldConflicts(
        array $conflicts,
        string $responseName,
        FieldNode $nodeA,
        FieldNode $nodeB
    ): ?Conflict {
        if (empty($conflicts)) {
            return null;
        }

        return new Conflict(
            $responseName,
            array_map(function (Conflict $conflict) {
                return [$conflict->getResponseName(), $conflict->getReason()];
            }, $conflicts),
            array_reduce($conflicts, function ($allFields, Conflict $conflict) {
                return array_merge($allFields, $conflict->getFieldsA());
            }, [$nodeA]),
            array_reduce($conflicts, function ($allFields, Conflict $conflict) {
                return array_merge($allFields, $conflict->getFieldsB());
            }, [$nodeB])
        );
    }
}
