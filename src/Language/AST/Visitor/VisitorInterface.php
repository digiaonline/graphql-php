<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Util\SerializationInterface;

interface VisitorInterface
{

    /**
     * @param NodeInterface|AcceptVisitorTrait $node
     * @param string|int|null                  $key
     * @param NodeInterface|null               $parent
     * @param array                            $path
     * @return NodeInterface|SerializationInterface|null
     */
    public function enterNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface;

    /**
     * @param NodeInterface|AcceptVisitorTrait $node
     * @param string|int|null                  $key
     * @param NodeInterface|null               $parent
     * @param array                            $path
     * @return NodeInterface|SerializationInterface|null
     */
    public function leaveNode(
        NodeInterface $node,
        $key = null,
        ?NodeInterface $parent = null,
        array $path = []
    ): ?NodeInterface;
}
