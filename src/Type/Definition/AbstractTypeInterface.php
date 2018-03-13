<?php

namespace Digia\GraphQL\Type\Definition;

interface AbstractTypeInterface extends TypeInterface
{
    public function getName(): string;

    public function resolveType(...$args);
}
