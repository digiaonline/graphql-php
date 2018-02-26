<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

interface VisitorInterface
{

    /**
     * @param NodeInterface $node
     * @param string|null $key
     * @param array $path
     * @return array|null
     */
    public function enterNode(NodeInterface $node, ?string $key = null, array $path = []): ?array;

    /**
     * @param NodeInterface $node
     * @param string|null $key
     * @param array $path
     * @return array|null
     */
    public function leaveNode(NodeInterface $node, ?string $key = null, array $path = []): ?array;
}
