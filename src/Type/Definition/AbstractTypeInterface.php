<?php

namespace Digia\GraphQL\Type\Definition;

use GraphQL\Contracts\TypeSystem\Type\AbstractTypeInterface as AbstractTypeContract;

interface AbstractTypeInterface extends AbstractTypeContract
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
