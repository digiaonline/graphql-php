<?php

namespace Digia\GraphQL\Language\AST\Visitor;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

interface AcceptVisitorInterface
{

    /**
     * @param VisitorInterface $visitor
     * @param string|null $key
     * @param array $path
     * @return NodeInterface|null
     */
    public function accept(VisitorInterface $visitor, ?string $key = null, array $path = []): ?NodeInterface;
}
