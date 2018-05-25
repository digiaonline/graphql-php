<?php

namespace Digia\GraphQL\Type\Definition;

interface AbstractTypeInterface extends NamedTypeInterface
{
    /**
     * @param array ...$args
     * @return mixed
     */
    public function resolveType(...$args);
}
