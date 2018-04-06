<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;

class BraceReader extends AbstractReader
{

    /**
     * @inheritdoc
     */
    public function read(
        int $code,
        int $pos,
        int $line,
        int $col,
        Token $prev
    ): Token {
        return $code === 123
            ? new Token(TokenKindEnum::BRACE_L, $pos, $pos + 1, $line, $col,
                $prev)
            : new Token(TokenKindEnum::BRACE_R, $pos, $pos + 1, $line, $col,
                $prev);
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        return $code === 123 || $code === 125; // { or }
    }
}
