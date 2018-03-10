<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\AST\Node\SelectionNodeInterface;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\TypeInfo;

class ValidationContext
{
    /**
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * @var DocumentNode
     */
    protected $documentNode;

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
     * ValidationContext constructor.
     * @param SchemaInterface $schema
     * @param DocumentNode    $documentNode
     * @param TypeInfo        $typeInfo
     */
    public function __construct(SchemaInterface $schema, DocumentNode $documentNode, TypeInfo $typeInfo)
    {
        $this->schema       = $schema;
        $this->documentNode = $documentNode;
        $this->typeInfo     = $typeInfo;
    }

    /**
     * @param ValidationException $error
     */
    public function reportError(ValidationException $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return array|ValidationException[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return TypeInterface|null
     */
    public function getParentType(): ?TypeInterface
    {
        return $this->typeInfo->getParentType();
    }

    /**
     * @return Field|null
     */
    public function getFieldDefinition(): ?Field
    {
        return $this->typeInfo->getFieldDefinition();
    }

    /**
     * @return SchemaInterface
     */
    public function getSchema(): SchemaInterface
    {
        return $this->schema;
    }

    /**
     * @return Argument|null
     */
    public function getArgument(): ?Argument
    {
        return $this->typeInfo->getArgument();
    }

    /**
     * @return Directive|null
     */
    public function getDirective(): ?Directive
    {
        return $this->typeInfo->getDirective();
    }

    /**
     * @param string $name
     * @return FragmentDefinitionNode|null
     */
    public function getFragment(string $name): ?FragmentDefinitionNode
    {
        if (empty($this->fragments)) {
            $this->fragments = array_reduce($this->documentNode->getDefinitions(), function ($fragments, $definition) {
                if ($definition instanceof FragmentDefinitionNode) {
                    $fragments[$definition->getNameValue()] = $definition;
                }
                return $fragments;
            }, []);
        }

        return $this->fragments[$name] ?? null;
    }

    /**
     * @param SelectionSetNode $selectionSet
     * @return array|FragmentSpreadNode[]
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
}
