<?php

namespace Digia\GraphQL\Type\Definition;

class TypeNameEnum
{

    public const INT = 'Int';

    public const FLOAT = 'Float';

    public const STRING = 'String';

    public const BOOLEAN = 'Boolean';

    public const ID = 'ID';

    public const INTERFACE = 'Interface';

    public const ENUM = 'Enum';

    public const UNION = 'Union';

    public const LIST = 'List';

    public const NULL = 'Null';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
