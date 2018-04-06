<?php

namespace Digia\GraphQL\Type\Definition;

interface WrappingTypeInterface extends TypeInterface
{

    /**
     * @return TypeInterface
     */
    public function getOfType(): TypeInterface;
}
