<?php

namespace Digia\GraphQL\Schema;

use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\NamedTypeNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\TypeSystemDefinitionNodeInterface;
use Digia\GraphQL\Type\Definition\Directive;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;

interface DefinitionBuilderInterface
{
    /**
     * @param NodeInterface[] $nodes
     * @return TypeInterface[]
     */
    public function buildTypes(array $nodes): array;

    /**
     * @param NodeInterface $node
     * @return NamedTypeInterface
     */
    public function buildType(NodeInterface $node): NamedTypeInterface;

    /**
     * @param DirectiveDefinitionNode $node
     * @return Directive
     */
    public function buildDirective(DirectiveDefinitionNode $node): Directive;

    // TODO: Introduce an interface for FieldDefinitionNode and InputValueDefinitionNode

    /**
     * @param FieldDefinitionNode|InputValueDefinitionNode $node
     * @param callable|null                                $resolve
     * @return array
     */
    public function buildField($node, ?callable $resolve = null): array;
}
