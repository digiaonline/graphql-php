<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\Node\NodeInterface;

interface NodeBuilderInterface
{

    /**
     * @param array $ast
     *
     * @return NodeInterface
     */
    public function build(array $ast): NodeInterface;
}
