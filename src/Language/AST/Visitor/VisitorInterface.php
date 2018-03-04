<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Util\SerializationInterface;

interface VisitorInterface
{

    /**
     * @param NodeInterface|AcceptVisitorTrait $node
     * @param string|null $key
     * @param array $path
     * @return NodeInterface|SerializationInterface|null
     */
    public function enterNode(NodeInterface $node, ?string $key = null, array $path = []): ?NodeInterface;

    /**
     * @param NodeInterface|AcceptVisitorTrait $node
     * @param string|null $key
     * @param array $path
     * @return NodeInterface|SerializationInterface|null
     */
    public function leaveNode(NodeInterface $node, ?string $key = null, array $path = []): ?NodeInterface;
}
