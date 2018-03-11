<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;

interface DirectorInterface
{

    /**
     * @param array $ast
     * @return NodeInterface
     */
    public function build(array $ast): NodeInterface;
}
