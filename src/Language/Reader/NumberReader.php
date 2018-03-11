<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;
use function Digia\GraphQL\Language\charCodeAt;
use function Digia\GraphQL\Language\isNumber;
use function Digia\GraphQL\Language\printCharCode;
use function Digia\GraphQL\Language\sliceString;

/**
 * Class NumberReader
 *
 * @package Digia\GraphQL\Language\Reader
 * Reads a number token from the source file, either a float
 * or an int depending on whether a decimal point appears.
 * Int:   -?(0|[1-9][0-9]*)
 * Float: -?(0|[1-9][0-9]*)(\.[0-9]+)?((E|e)(+|-)?[0-9]+)?
 */
class NumberReader extends AbstractReader
{

    /**
     * @inheritdoc
     */
    public function read(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        $body    = $this->lexer->getBody();
        $start   = $pos;
        $isFloat = false;

        if ($code === 45) {
            // -
            $code = charCodeAt($body, ++$pos);
        }

        if ($code === 48) {
            // 0
            $code = charCodeAt($body, ++$pos);
            if (isNumber($code)) {
                throw new SyntaxErrorException(
                    $this->lexer->getSource(),
                    $pos,
                    sprintf('Invalid number, unexpected digit after 0: %s.', printCharCode($code))
                );
            }
        } else {
            $pos  = $this->readDigits($code, $pos);
            $code = charCodeAt($body, $pos);
        }

        if ($code === 46) {
            // .
            $isFloat = true;
            $code    = charCodeAt($body, ++$pos);
            $pos     = $this->readDigits($code, $pos);
            $code    = charCodeAt($body, $pos);
        }

        if ($code === 69 || $code === 101) {
            // e or E
            $isFloat = true;
            $code    = charCodeAt($body, ++$pos);

            if ($code === 43 || $code === 45) {
                // + or -
                $code = charCodeAt($body, ++$pos);
            }

            $pos = $this->readDigits($code, $pos);
        }

        return new Token(
            $isFloat ? TokenKindEnum::FLOAT : TokenKindEnum::INT,
            $start,
            $pos,
            $line,
            $col,
            $prev,
            sliceString($body, $start, $pos)
        );
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        return isNumber($code);
    }

    /**
     * @param int $code
     * @param int $pos
     * @return int
     * @throws SyntaxErrorException
     */
    protected function readDigits(int $code, int $pos): int
    {
        $body = $this->lexer->getBody();

        if (isNumber($code)) {
            do {
                $code = charCodeAt($body, ++$pos);
            } while (isNumber($code));

            return $pos;
        }

        throw new SyntaxErrorException(
            $this->lexer->getSource(),
            $pos,
            sprintf('Invalid number, expected digit but got: %s', printCharCode($code))
        );
    }
}
