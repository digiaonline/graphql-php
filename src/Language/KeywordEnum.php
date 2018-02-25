<?php

namespace Digia\GraphQL\Language;

class KeywordEnum
{

    const SCHEMA       = 'schema';
    const SCALAR       = 'scalar';
    const TYPE         = 'type';
    const INTERFACE    = 'interface';
    const UNION        = 'union';
    const ENUM         = 'enum';
    const INPUT        = 'input';
    const EXTEND       = 'extend';
    const DIRECTIVE    = 'directive';
    const ON           = 'on';
    const FRAGMENT     = 'fragment';
    const QUERY        = 'query';
    const MUTATION     = 'mutation';
    const SUBSCRIPTION = 'subscription';
    const TRUE         = 'true';
    const FALSE        = 'false';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
