<?php

namespace Digia\GraphQL\Type\Definition;

class SpecifiedDirectiveEnum
{

    public const INCLUDE = 'include';

    public const SKIP = 'skip';

    public const DEPRECATED = 'deprecated';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
