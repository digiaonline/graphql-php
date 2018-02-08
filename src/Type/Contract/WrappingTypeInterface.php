<?php

namespace Digia\GraphQL\Type\Contract;

interface WrappingTypeInterface
{

    /**
     * @return TypeInterface
     */
    public function getOfType(): TypeInterface;
}
