<?php

namespace Digia\GraphQL\Type\Definition;

interface AbstractTypeInterface extends TypeInterface
{
    /**
     * @param array ...$args
     * @return mixed
     */
    public function resolveType(...$args);
}
