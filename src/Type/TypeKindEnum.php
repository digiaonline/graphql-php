<?php

namespace Digia\GraphQL\Type;

class TypeKindEnum
{

    public const SCALAR       = 'SCALAR';
    public const OBJECT       = 'OBJECT';
    public const INTERFACE    = 'INTERFACE';
    public const UNION        = 'UNION';
    public const ENUM         = 'ENUM';
    public const INPUT_OBJECT = 'INPUT_OBJECT';
    public const LIST         = 'LIST';
    public const NON_NULL     = 'NON_NULL';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
