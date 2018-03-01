<?php

namespace Digia\GraphQL\Type\Definition;

interface WrappingTypeInterface
{

    /**
     * @return TypeInterface
     */
    public function getOfType(): TypeInterface;
}
