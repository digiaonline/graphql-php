<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

trait ParseValueTrait
{

    /**
     * @param array $ast
     * @return string|null
     */
    protected function parseValue(array $ast): ?string
    {
        return $ast['value'] ?? null;
    }
}
