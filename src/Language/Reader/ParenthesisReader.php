<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;

class ParenthesisReader extends AbstractReader
{

    /**
     * @inheritdoc
     */
    public function read(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        return $code === 40
            ? new Token(TokenKindEnum::PAREN_L, $pos, $pos + 1, $line, $col, $prev)
            : new Token(TokenKindEnum::PAREN_R, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        return $code === 40 || $code === 41; // ( or )
    }
}
