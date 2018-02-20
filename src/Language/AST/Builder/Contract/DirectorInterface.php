<?php

namespace Digia\GraphQL\Language\AST\Builder\Contract;

use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

interface DirectorInterface
{

    /**
     * @param array $ast
     * @return NodeInterface
     */
    public function build(array $ast): NodeInterface;
}
