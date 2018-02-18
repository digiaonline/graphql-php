<?php

namespace Digia\GraphQL\Language\Reader;

use function Digia\GraphQL\Language\charCodeAt;
use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;

class SpreadReader extends AbstractReader
{

    /**
     * @inheritdoc
     */
    public function read(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        return new Token(TokenKindEnum::SPREAD, $pos, $pos + 3, $line, $col, $prev);
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        $body = $this->lexer->getBody();

        return $code === 46 && charCodeAt($body, $pos + 1) === 46 && charCodeAt($body, $pos + 2) === 46; // ...
    }
}
