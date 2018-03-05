<?php

namespace Digia\GraphQL\Type;

class TypeKindEnum
{

    const SCALAR       = 'SCALAR';
    const OBJECT       = 'OBJECT';
    const INTERFACE    = 'INTERFACE';
    const UNION        = 'UNION';
    const ENUM         = 'ENUM';
    const INPUT_OBJECT = 'INPUT_OBJECT';
    const LIST         = 'LIST';
    const NON_NULL     = 'NON_NULL';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
