<?php

namespace Digia\GraphQL\Type\Definition;

use GraphQL\Contracts\TypeSystem\Type\NamedTypeInterface;

interface AbstractTypeInterface extends NamedTypeInterface
{
    /**
     * @param array ...$args
     * @return mixed
     */
    public function resolveType(...$args);

    /**
     * @return bool
     */
    public function hasResolveTypeCallback(): bool;
}
