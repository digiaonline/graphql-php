<?php

namespace Digia\GraphQL\Validation;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Language\Node\VariableDefinitionsAwareInterface;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\Argument;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\TypeInterface;

interface ValidationContextInterface
{
    /**
     * @param ValidationException $error
     */
    public function reportError(ValidationException $error): void;

    /**
     * @return array|ValidationException[]
     */
    public function getErrors(): array;

    /**
     * @return TypeInterface|null
     */
    public function getType(): ?TypeInterface;

    /**
     * @return TypeInterface|null
     */
    public function getParentType(): ?TypeInterface;

    /**
     * @return TypeInterface|null
     */
    public function getInputType(): ?TypeInterface;

    /**
     * @return TypeInterface|null
     */
    public function getParentInputType(): ?TypeInterface;

    /**
     * @return Field|null
     */
    public function getFieldDefinition(): ?Field;

    /**
     * @return Schema
     */
    public function getSchema(): Schema;

    /**
     * @return Argument|null
     */
    public function getArgument(): ?Argument;

    /**
     * @return Directive|null
     */
    public function getDirective(): ?Directive;

    /**
     * @param string $name
     * @return FragmentDefinitionNode|null
     */
    public function getFragment(string $name): ?FragmentDefinitionNode;

    /**
     * @param SelectionSetNode $selectionSet
     * @return array|FragmentSpreadNode[]
     */
    public function getFragmentSpreads(SelectionSetNode $selectionSet): array;

    /**
     * @param OperationDefinitionNode $operation
     * @return array
     */
    public function getRecursiveVariableUsages(OperationDefinitionNode $operation): array;

    /**
     * @param NodeInterface $node
     * @return array
     */
    public function getVariableUsages(NodeInterface $node): array;

    /**
     * @param OperationDefinitionNode $operation
     * @return array|FragmentDefinitionNode[]
     */
    public function getRecursivelyReferencedFragments(OperationDefinitionNode $operation): array;
}
