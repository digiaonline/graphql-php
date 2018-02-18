<?php

namespace Digia\GraphQL\Language;

class TokenKindEnum
{

    const SOF          = '<SOF>';
    const EOF          = '<EOF>';
    const BANG         = '!';
    const DOLLAR       = '$';
    const AMP          = '&';
    const PAREN_L      = '(';
    const PAREN_R      = ')';
    const SPREAD       = '...';
    const COLON        = ':';
    const EQUALS       = '=';
    const AT           = '@';
    const BRACKET_L    = '[';
    const BRACKET_R    = ']';
    const BRACE_L      = '{';
    const PIPE         = '|';
    const BRACE_R      = '}';
    const NAME         = 'Name';
    const INT          = 'Int';
    const FLOAT        = 'Float';
    const STRING       = 'String';
    const BLOCK_STRING = 'BlockString';
    const COMMENT      = 'Comment';

    /**
     * @return array
     * @throws \ReflectionException
     */
    public static function values(): array
    {
        return array_values((new \ReflectionClass(__CLASS__))->getConstants());
    }
}
