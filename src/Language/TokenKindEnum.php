<?php

namespace Digia\GraphQL\Language;

class TokenKindEnum
{

    public const SOF          = '<SOF>';
    public const EOF          = '<EOF>';
    public const BANG         = '!';
    public const DOLLAR       = '$';
    public const AMP          = '&';
    public const PAREN_L      = '(';
    public const PAREN_R      = ')';
    public const SPREAD       = '...';
    public const COLON        = ':';
    public const EQUALS       = '=';
    public const AT           = '@';
    public const BRACKET_L    = '[';
    public const BRACKET_R    = ']';
    public const BRACE_L      = '{';
    public const PIPE         = '|';
    public const BRACE_R      = '}';
    public const NAME         = 'Name';
    public const INT          = 'Int';
    public const FLOAT        = 'Float';
    public const STRING       = 'String';
    public const BLOCK_STRING = 'BlockString';
    public const COMMENT      = 'Comment';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
