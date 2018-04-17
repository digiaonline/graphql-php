<?php

namespace Digia\GraphQL\Schema;

use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\FieldDefinitionNode;
use Digia\GraphQL\Language\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\Directive;
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
     * @return TypeInterface
     */
    public function buildType(NodeInterface $node): TypeInterface;

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
