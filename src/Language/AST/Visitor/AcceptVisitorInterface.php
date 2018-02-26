<?php

namespace Digia\GraphQL\Language\AST\Visitor;

interface AcceptVisitorInterface
{

    /**
     * @param VisitorInterface $visitor
     * @param string|null $key
     * @param array $path
     * @return array|null
     */
    public function accept(VisitorInterface $visitor, ?string $key = null, array $path = []): ?array;
}
