<?php

namespace Digia\GraphQL\SchemaBuilder;

use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\DirectiveInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;

interface DefinitionBuilderInterface
{
    /**
     * @param NodeInterface $node
     * @return TypeInterface
     */
    public function buildType(NodeInterface $node): TypeInterface;

    /**
     * @param DirectiveDefinitionNode $node
     * @return DirectiveInterface
     */
    public function buildDirective(DirectiveDefinitionNode $node): DirectiveInterface;

    /**
     * @param array $typeDefinitionMap
     * @return $this
     */
    public function setTypeDefinitionMap(array $typeDefinitionMap);

    /**
     * @param array $resolverMap
     * @return $this
     */
    public function setResolverMap(array $resolverMap);
}
