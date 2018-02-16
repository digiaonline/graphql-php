<?php

namespace Digia\GraphQL\Type\Definition;

class SpecifiedDirectiveEnum
{

    const INCLUDE    = 'include';
    const SKIP       = 'skip';
    const DEPRECATED = 'deprecated';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
