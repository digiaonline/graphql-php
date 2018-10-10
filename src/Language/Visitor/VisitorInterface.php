<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;

interface VisitorInterface
{
    /**
     * @param NodeInterface $node
     * @return VisitorResult
     */
    public function enterNode(NodeInterface $node): VisitorResult;

    /**
     * @param NodeInterface $node
     * @return VisitorResult
     */
    public function leaveNode(NodeInterface $node): VisitorResult;
}
