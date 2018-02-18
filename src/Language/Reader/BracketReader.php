<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;

class BracketReader extends AbstractReader
{

    /**
     * @inheritdoc
     */
    public function read(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        return $code === 91
            ? new Token(TokenKindEnum::BRACKET_L, $pos, $pos + 1, $line, $col, $prev)
            : new Token(TokenKindEnum::BRACKET_R, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        return $code === 91 || $code === 93; // [ or ]
    }
}
