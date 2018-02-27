<?php

namespace Digia\GraphQL\Language\AST\Schema;

use Digia\GraphQL\Language\AST\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
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
}
