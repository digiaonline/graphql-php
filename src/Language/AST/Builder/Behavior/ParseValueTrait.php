<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\NodeFactoryInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

trait ParseValueTrait
{

    /**
     * @return NodeFactoryInterface
     */
    abstract protected function getFactory(): NodeFactoryInterface;

    /**
     * @param array $ast
     * @return string|null
     */
    protected function parseValue(array $ast): ?string
    {
        return $ast['value'] ?? null;
    }
}
