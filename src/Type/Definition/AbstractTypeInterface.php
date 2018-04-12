<?php

namespace Digia\GraphQL\Type\Definition;

interface AbstractTypeInterface extends TypeInterface
{
    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param array ...$args
     * @return mixed
     */
    public function resolveType(...$args);
}
