<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

interface NodeBuilderInterface
{

    /**
     * @param array $ast
     * @return NodeInterface
     */
    public function build(array $ast): NodeInterface;
}
