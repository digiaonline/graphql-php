<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

trait ParseKindTrait
{

    /**
     * @param array $ast
     * @return string
     */
    protected function parseKind(array $ast): string
    {
        return $ast['kind'];
    }
}
