<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\typeFromAST;

class FieldCollector
{
    /**
     * @var ExecutionContext
     */
    protected $context;

    /**
     * FieldCollector constructor.
     * @param ExecutionContext $context
     */
    public function __construct(ExecutionContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param ObjectType       $runtimeType
     * @param SelectionSetNode $selectionSet
     * @param array            $fields
     * @param array            $visitedFragmentNames
     * @return array
     * @throws InvalidTypeException
     * @throws ExecutionException
     * @throws InvariantException
     */
    public function collectFields(
        ObjectType $runtimeType,
        SelectionSetNode $selectionSet,
        array &$fields,
        array &$visitedFragmentNames
    ): array {
        foreach ($selectionSet->getSelections() as $selection) {
            // Check if this Node should be included first
            if (!$this->shouldIncludeNode($selection)) {
                continue;
            }

            // Collect fields
            if ($selection instanceof FieldNode) {
                $fieldName = $selection->getAliasOrNameValue();

                if (!isset($fields[$fieldName])) {
                    $fields[$fieldName] = [];
                }

                $fields[$fieldName][] = $selection;

                continue;
            }

            if ($selection instanceof InlineFragmentNode) {
                if (!$this->doesFragmentConditionMatch($selection, $runtimeType)) {
                    continue;
                }

                $this->collectFields($runtimeType, $selection->getSelectionSet(), $fields, $visitedFragmentNames);

                continue;
            }

            if ($selection instanceof FragmentSpreadNode) {
                $fragmentName = $selection->getNameValue();

                if (!empty($visitedFragmentNames[$fragmentName])) {
                    continue;
                }

                $visitedFragmentNames[$fragmentName] = true;
                /** @var FragmentDefinitionNode $fragment */
                $fragment = $this->context->getFragments()[$fragmentName];
                $this->collectFields($runtimeType, $fragment->getSelectionSet(), $fields, $visitedFragmentNames);

                continue;
            }
        }

        return $fields;
    }

    /**
     * @param NodeInterface $node
     * @return bool
     */
    protected function shouldIncludeNode(NodeInterface $node): bool
    {
        $contextVariables = $this->context->getVariableValues();

        $skip = coerceDirectiveValues(SkipDirective(), $node, $contextVariables);

        if ($skip && $skip['if'] === true) {
            return false;
        }

        $include = coerceDirectiveValues(IncludeDirective(), $node, $contextVariables);

        /** @noinspection IfReturnReturnSimplificationInspection */
        if ($include && $include['if'] === false) {
            return false;
        }

        return true;
    }

    /**
     * @param FragmentDefinitionNode|InlineFragmentNode|NodeInterface $fragment
     * @param ObjectType                                              $type
     * @return bool
     * @throws InvariantException
     */
    protected function doesFragmentConditionMatch(NodeInterface $fragment, ObjectType $type): bool
    {
        $typeConditionNode = $fragment->getTypeCondition();

        if (null === $typeConditionNode) {
            return true;
        }

        /** @var ObjectType|TypeInterface $conditionalType */
        $conditionalType = typeFromAST($this->context->getSchema(), $typeConditionNode);

        if ($type === $conditionalType) {
            return true;
        }

        if ($conditionalType instanceof AbstractTypeInterface) {
            return $this->context->getSchema()->isPossibleType($conditionalType, $type);
        }

        return false;
    }

}
