<?php

namespace Digia\GraphQL\Language\Reader;

use Digia\GraphQL\Error\SyntaxError;
use function Digia\GraphQL\Language\charCodeAt;
use function Digia\GraphQL\Language\printCharCode;
use function Digia\GraphQL\Language\sliceString;
use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;
use function Digia\GraphQL\Language\uniCharCode;

/**
 * Class StringReader
 *
 * @package Digia\GraphQL\Language\Reader
 *
 * Reads a string token from the source file.
 *
 * "([^"\\\u000A\u000D]|(\\(u[0-9a-fA-F]{4}|["\\/bfnrt])))*"
 */
class StringReader extends AbstractReader
{

    /**
     * @inheritdoc
     * @throws SyntaxError
     */
    public function read(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        $body       = $this->lexer->getBody();
        $bodyLength = mb_strlen($body);
        $start      = $pos;
        $pos        = $start + 1;
        $chunkStart = $pos;
        $value      = '';

        while ($pos < $bodyLength && ($code = charCodeAt($body, $pos)) !== null && !$this->isLineTerminator($code)) {
            // Closing Quote (")
            if ($code === 34) {
                $value .= sliceString($body, $chunkStart, $pos);

                return new Token(TokenKindEnum::STRING, $start, $pos + 1, $line, $col, $prev, $value);
            }

            if ($this->isSourceCharacter($code)) {
                throw new SyntaxError(sprintf('Invalid character within String: %s', printCharCode($code)));
            }

            ++$pos;

            if ($code === 92) {
                $value .= sliceString($body, $chunkStart, $pos + 1);
                $code  = charCodeAt($body, $pos);

                switch ($code) {
                    case 34:
                        $value .= '"';
                        break;
                    case 47:
                        $value .= '/';
                        break;
                    case 92:
                        $value .= '\\';
                        break;
                    case 98:
                        $value .= '\b';
                        break;
                    case 102:
                        $value .= '\f';
                        break;
                    case 110:
                        $value .= '\n';
                        break;
                    case 114:
                        $value .= '\r';
                        break;
                    case 116:
                        $value .= '\t';
                        break;
                    case 117:
                        $charCode = uniCharCode(
                            charCodeAt($body, $pos + 1),
                            charCodeAt($body, $pos + 2),
                            charCodeAt($body, $pos + 3),
                            charCodeAt($body, $pos + 4)
                        );
                        if ($charCode < 0) {
                            throw new SyntaxError(
                                sprintf(
                                    'Invalid character escape sequence: \\u%s',
                                    sliceString($body, $pos + 1, $pos + 5)
                                )
                            );
                        }
                        $value .= chr($charCode);
                        $pos   += 4;
                        break;
                    default:
                        throw new SyntaxError(sprintf('Invalid character escape sequence: \\%s', chr($code)));
                }

                ++$pos;
                $chunkStart = $pos;
            }
        }

        throw new SyntaxError('Unterminated string.');
    }

    /**
     * @inheritdoc
     */
    public function supportsReader(int $code, int $pos): bool
    {
        $body = $this->lexer->getBody();

        return $code === 34 && charCodeAt($body, $pos + 1) !== 34;
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function isLineTerminator(int $code): bool
    {
        return $code === 0x000a || $code === 0x000d;
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function isSourceCharacter(int $code): bool
    {
        return $code < 0x0020 && $code !== 0x0009;
    }
}
