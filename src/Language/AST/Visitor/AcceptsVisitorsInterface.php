<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

interface AcceptsVisitorsInterface
{

    /**
     * @param VisitorInterface      $visitor
     * @param string|int|null       $key
     * @param NodeInterface|null    $parent
     * @param array|string[]        $path
     * @param array|NodeInterface[] $ancestors
     * @return NodeInterface|null
     */
    public function acceptVisitor(
        VisitorInterface $visitor,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = [],
        array $ancestors = []
    ): ?NodeInterface;
}
