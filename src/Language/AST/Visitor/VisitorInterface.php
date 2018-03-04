<?php

namespace Digia\GraphQL\Language\AST\Visitor;

interface VisitorInterface
{

    /**
     * @param array $node
     * @param string|null $key
     * @param array $path
     * @return array|null
     */
    public function enterNode(array $node, ?string $key = null, array $path = []): ?array;

    /**
     * @param array $node
     * @param string|null $key
     * @param array $path
     * @return array|null
     */
    public function leaveNode(array $node, ?string $key = null, array $path = []): ?array;
}
