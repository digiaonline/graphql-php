<?php

namespace Digia\GraphQL\Language\Reader;

use function Digia\GraphQL\Language\charCodeAt;
use function Digia\GraphQL\Language\sliceString;
use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;

class CommentReader extends AbstractReader
{

    /**
     * @inheritdoc
     */
    public function read(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        $body  = $this->lexer->getBody();
        $start = $pos;

        do {
            $code = charCodeAt($body, ++$pos);
        } while ($code !== null && ($code > 0x001f || $code === 0x0009)); // SourceCharacter but not LineTerminator

        return new Token(
            TokenKindEnum::COMMENT,
            $start,
            $pos,
            $line,
            $col,
            $prev,
            sliceString($body, $start + 1, $pos)
        );
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        return $code === 35; // #
    }
}
