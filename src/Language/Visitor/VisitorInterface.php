<?php

namespace Digia\GraphQL\Language\Visitor;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Util\SerializationInterface;

interface VisitorInterface
{

    /**
     * @param NodeInterface|AcceptsVisitorsTrait $node
     *
     * @return NodeInterface|SerializationInterface|null
     */
    public function enterNode(NodeInterface $node): ?NodeInterface;

    /**
     * @param NodeInterface|AcceptsVisitorsTrait $node
     *
     * @return NodeInterface|SerializationInterface|null
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface;
}
