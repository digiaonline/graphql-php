<?php

namespace Digia\GraphQL\Type\Definition\Contract;

interface WrappingTypeInterface
{

    /**
     * @return TypeInterface
     */
    public function getOfType(): TypeInterface;
}
