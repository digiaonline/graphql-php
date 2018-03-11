<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;
use function Digia\GraphQL\Language\blockStringValue;
use function Digia\GraphQL\Language\charCodeAt;
use function Digia\GraphQL\Language\isSourceCharacter;
use function Digia\GraphQL\Language\printCharCode;
use function Digia\GraphQL\Language\sliceString;

/**
 * Class BlockStringReader
 *
 * @package Digia\GraphQL\Language\Reader
 * Reads a block string token from the source file.
 * """("?"?(\\"""|\\(?!=""")|[^"\\]))*"""
 */
class BlockStringReader extends AbstractReader
{

    /**
     * @inheritdoc
     */
    public function read(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        $body       = $this->lexer->getBody();
        $bodyLength = mb_strlen($body);
        $start      = $pos;
        $pos        = $start + 3;
        $chunkStart = $pos;
        $rawValue   = '';

        while ($pos < $bodyLength && ($code = charCodeAt($body, $pos)) !== null) {
            // Closing Triple-Quote (""")
            if ($this->isTripleQuote($body, $code, $pos)) {
                $rawValue .= sliceString($body, $chunkStart, $pos);

                return new Token(
                    TokenKindEnum::BLOCK_STRING,
                    $start,
                    $pos + 3,
                    $line,
                    $col,
                    $prev,
                    blockStringValue($rawValue)
                );
            }

            if (isSourceCharacter($code)) {
                throw new SyntaxErrorException(
                    $this->lexer->getSource(),
                    $pos,
                    sprintf('Invalid character within String: %s', printCharCode($code))
                );
            }

            if ($this->isEscapedTripleQuote($body, $code, $pos)) {
                $rawValue   .= sliceString($body, $chunkStart, $pos) . '"""';
                $pos        += 4;
                $chunkStart = $pos;
            } else {
                ++$pos;
            }
        }

        throw new SyntaxErrorException($this->lexer->getSource(), $pos, 'Unterminated string.');
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        return $this->isTripleQuote($this->lexer->getBody(), $code, $pos);
    }

    /**
     * @param string $body
     * @param int    $code
     * @param int    $pos
     * @return bool
     */
    protected function isTripleQuote(string $body, int $code, int $pos): bool
    {
        return $code === 34 && charCodeAt($body, $pos + 1) === 34 && charCodeAt($body, $pos + 2) === 34; // """
    }

    /**
     * @param string $body
     * @param int    $code
     * @param int    $pos
     * @return bool
     */
    protected function isEscapedTripleQuote(string $body, int $code, int $pos): bool
    {
        return $code === 92 &&
            charCodeAt($body, $pos + 1) === 34 &&
            charCodeAt($body, $pos + 2) === 34 &&
            charCodeAt($body, $pos + 3) === 34;
    }
}
