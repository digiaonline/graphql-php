<?php

namespace Digia\GraphQL\Type\Definition;

class TypeNameEnum
{

    const INT       = 'Int';
    const FLOAT     = 'Float';
    const STRING    = 'String';
    const BOOLEAN   = 'Boolean';
    const ID        = 'ID';
    const INTERFACE = 'Interface';
    const ENUM      = 'Enum';
    const UNION     = 'Union';
    const LIST      = 'List';
    const NULL      = 'Null';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
