<?php

namespace Digia\GraphQL\Language;

class KeywordEnum
{

    public const SCHEMA       = 'schema';
    public const SCALAR       = 'scalar';
    public const TYPE         = 'type';
    public const INTERFACE    = 'interface';
    public const UNION        = 'union';
    public const ENUM         = 'enum';
    public const INPUT        = 'input';
    public const EXTEND       = 'extend';
    public const DIRECTIVE    = 'directive';
    public const ON           = 'on';
    public const FRAGMENT     = 'fragment';
    public const QUERY        = 'query';
    public const MUTATION     = 'mutation';
    public const SUBSCRIPTION = 'subscription';
    public const TRUE         = 'true';
    public const FALSE        = 'false';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
