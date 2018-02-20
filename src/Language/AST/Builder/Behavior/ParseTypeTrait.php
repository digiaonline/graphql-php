<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

trait ParseTypeTrait
{

    /**
     * @param array $ast
     * @return string
     */
    protected function parseType(array $ast): string
    {
        return $ast['type'];
    }
}
