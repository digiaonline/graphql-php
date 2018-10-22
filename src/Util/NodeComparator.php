<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Language\Node\NodeInterface;

class NodeComparator
{
    /**
     * @param NodeInterface $node
     * @param NodeInterface $other
     * @return bool
     */
    public static function compare(NodeInterface $node, NodeInterface $other): bool
    {
        return $node->toJSON() === $other->toJSON();
    }
}
