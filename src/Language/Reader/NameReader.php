<?php

namespace Digia\GraphQL\Language\Reader;

use function Digia\GraphQL\Language\charCodeAt;
use function Digia\GraphQL\Language\sliceString;
use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;

class NameReader extends AbstractReader
{

    /**
     * @inheritdoc
     */
    public function read(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        $body       = $this->lexer->getBody();
        $bodyLength = mb_strlen($body);
        $start      = $pos;
        $pos        = $start + 1;

        while ($pos !== $bodyLength && ($code = charCodeAt($body, $pos)) !== null && $this->isAlphaNumeric($code)) {
            ++$pos;
        }

        return new Token(TokenKindEnum::NAME, $start, $pos, $line, $col, $prev, sliceString($body, $start, $pos));
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        return $this->isLetter($code) || $this->isUnderscore($code);
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function isLetter(int $code): bool
    {
        return ($code >= 65 && $code <= 90) || ($code >= 97 && $code <= 122); // a-z or A-Z
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function isNumber(int $code): bool
    {
        return $code >= 48 && $code <= 57; // 0-9
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function isUnderscore(int $code): bool
    {
        return $code === 95; // _
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function isAlphaNumeric(int $code): bool
    {
        return $this->isLetter($code) || $this->isNumber($code) || $this->isUnderscore($code);
    }
}
