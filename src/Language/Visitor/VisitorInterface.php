<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;

interface VisitorInterface
{
    /**
     * @param NodeInterface $node
     * @return NodeInterface|null
     */
    public function enterNode(NodeInterface $node): ?NodeInterface;

    /**
     * @param NodeInterface $node
     * @return NodeInterface|null
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface;
}
