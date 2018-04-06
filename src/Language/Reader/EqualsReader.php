<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;

class EqualsReader extends AbstractReader
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
        return new Token(TokenKindEnum::EQUALS, $pos, $pos + 1, $line, $col,
            $prev);
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        return $code === 61; // =
    }
}
