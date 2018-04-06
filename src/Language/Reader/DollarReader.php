<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;

class DollarReader extends AbstractReader
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
        return new Token(TokenKindEnum::DOLLAR, $pos, $pos + 1, $line, $col,
            $prev);
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        return $code === 36; // $
    }
}
