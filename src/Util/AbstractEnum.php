<?php

namespace Digia\GraphQL\Util;

abstract class AbstractEnum
{
    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return \array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
