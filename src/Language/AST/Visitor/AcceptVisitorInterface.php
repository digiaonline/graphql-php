<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

interface AcceptVisitorInterface
{

    /**
     * @param VisitorInterface $visitor
     * @param string|int|null $key
     * @param NodeInterface|null $parent
     * @param array $path
     * @return NodeInterface|null
     */
    public function accept(
        VisitorInterface $visitor,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface;
}
