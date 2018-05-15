<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Language\Visitor\AcceptsVisitorsInterface;
use Digia\GraphQL\Language\Visitor\TypeInfoVisitor;
use Digia\GraphQL\Language\Visitor\Visitor;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Util\TypeInfo;

class ValidationContext implements ValidationContextInterface
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var DocumentNode
     */
    protected $document;

    /**
     * @var TypeInfo
     */
    protected $typeInfo;

    /**
     * @var array|ValidationException[]
     */
    protected $errors = [];

    /**
     * @var array|FragmentDefinitionNode[]
     */
    protected $fragments = [];

    /**
     * @var array
     */
    protected $fragmentSpreads = [];

    /**
     * @var array
     */
    protected $variableUsages = [];

    /**
     * @var array
     */
    protected $recursiveVariableUsages = [];

    /**
     * @var array
     */
    protected $recursivelyReferencedFragment = [];

    /**
     * ValidationContext constructor.
     * @param Schema       $schema
     * @param DocumentNode $document
     * @param TypeInfo     $typeInfo
     */
    public function __construct(Schema $schema, DocumentNode $document, TypeInfo $typeInfo)
    {
        $this->schema   = $schema;
        $this->document = $document;
        $this->typeInfo = $typeInfo;
    }

    /**
     * @inheritdoc
     */
    public function reportError(ValidationException $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return TypeInterface|null
     */
    public function getType(): ?TypeInterface
    {
        return $this->typeInfo->getType();
    }

    /**
     * @inheritdoc
     */
    public function getParentType(): ?TypeInterface
    {
        return $this->typeInfo->getParentType();
    }

    /**
     * @inheritdoc
     */
    public function getInputType(): ?TypeInterface
    {
        return $this->typeInfo->getInputType();
    }

    /**
     * @inheritdoc
     */
    public function getParentInputType(): ?TypeInterface
    {
        return $this->typeInfo->getParentInputType();
    }

    /**
     * @inheritdoc
     */
    public function getFieldDefinition(): ?Field
    {
        return $this->typeInfo->getFieldDefinition();
    }

    /**
     * @inheritdoc
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @inheritdoc
     */
    public function getArgument(): ?Argument
    {
        return $this->typeInfo->getArgument();
    }

    /**
     * @inheritdoc
     */
    public function getDirective(): ?Directive
    {
        return $this->typeInfo->getDirective();
    }

    /**
     * @inheritdoc
     */
    public function getFragment(string $name): ?FragmentDefinitionNode
    {
        if (empty($this->fragments)) {
            $this->fragments = array_reduce($this->document->getDefinitions(), function ($fragments, $definition) {
                if ($definition instanceof FragmentDefinitionNode) {
                    $fragments[$definition->getNameValue()] = $definition;
                }
                return $fragments;
            }, []);
        }

        return $this->fragments[$name] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getFragmentSpreads(SelectionSetNode $selectionSet): array
    {
        $spreads = $this->fragmentSpreads[(string)$selectionSet] ?? null;

        if (null === $spreads) {
            $spreads = [];

            $setsToVisit = [$selectionSet];

            while (!empty($setsToVisit)) {
                /** @var SelectionSetNode $set */
                $set = array_pop($setsToVisit);

                /** @var FieldNode $selection */
                foreach ($set->getSelections() as $selection) {
                    if ($selection instanceof FragmentSpreadNode) {
                        $spreads[] = $selection;
                    } elseif ($selection->hasSelectionSet()) {
                        $setsToVisit[] = $selection->getSelectionSet();
                    }
                }
            }

            $this->fragmentSpreads[(string)$selectionSet] = $spreads;
        }

        return $spreads;
    }

    /**
     * @inheritdoc
     */
    public function getRecursiveVariableUsages(OperationDefinitionNode $operation): array
    {
        $usages = $this->recursiveVariableUsages[(string)$operation] ?? null;

        if (null === $usages) {
            $usages    = $this->getVariableUsages($operation);
            $fragments = $this->getRecursivelyReferencedFragments($operation);

            foreach ($fragments as $fragment) {
                // TODO: Figure out a more performance way to do this.
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $usages = array_merge($usages, $this->getVariableUsages($fragment));
            }

            $this->recursiveVariableUsages[(string)$operation] = $usages;
        }

        return $usages;
    }

    /**
     * @inheritdoc
     */
    public function getVariableUsages($node): array
    {
        $usages = $this->variableUsages[(string)$node] ?? null;

        if (null === $usages) {
            $usages   = [];
            $typeInfo = new TypeInfo($this->schema);
            $visitor  = new TypeInfoVisitor($typeInfo, new Visitor(
                function (NodeInterface $node) use (&$usages, $typeInfo): ?NodeInterface {
                    if ($node instanceof VariableDefinitionNode) {
                        return null;
                    }

                    if ($node instanceof VariableNode) {
                        $usages[] = ['node' => $node, 'type' => $typeInfo->getInputType()];
                    }

                    return $node;
                }
            ));

            $node->acceptVisitor($visitor);

            $this->variableUsages[(string)$node] = $usages;
        }

        return $usages;
    }

    /**
     * @inheritdoc
     */
    public function getRecursivelyReferencedFragments(OperationDefinitionNode $operation): array
    {
        $fragments = $this->recursivelyReferencedFragment[(string)$operation] ?? null;

        if (null === $fragments) {
            $fragments      = [];
            $collectedNames = [];
            $nodesToVisit   = [$operation->getSelectionSet()];

            while (!empty($nodesToVisit)) {
                $node    = array_pop($nodesToVisit);
                $spreads = $this->getFragmentSpreads($node);

                foreach ($spreads as $spread) {
                    $fragmentName = $spread->getNameValue();

                    if (!isset($collectedNames[$fragmentName])) {
                        $collectedNames[$fragmentName] = true;
                        $fragment                      = $this->getFragment($fragmentName);

                        if (null !== $fragment) {
                            $fragments[]    = $fragment;
                            $nodesToVisit[] = $fragment->getSelectionSet();
                        }
                    }
                }
            }

            $this->recursivelyReferencedFragment[(string)$operation] = $fragments;
        }

        return $fragments;
    }
}
