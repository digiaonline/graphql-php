<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\SyntaxErrorException;

class TokenReader implements TokenReaderInterface
{
    protected const PATTERN_COMMENT      = "/#[\u{0009}\u{0020}-\u{FFFF}]*/A";
    protected const PATTERN_NAME         = '/[_A-Za-z][_0-9A-Za-z]*/A';
    protected const PATTERN_INT          = '/((?!\.)(-?(0|[1-9][0-9]*)))+$/A';
    protected const PATTERN_FLOAT        = '/-?(0|[1-9][0-9]*)(\.[0-9]+)?((E|e)(\+|-)?[0-9]+)?/A';
    protected const PATTERN_SPREAD       = '/\.\.\./A';
    protected const PATTERN_STRING       = "/\"([^\"\\\u{000A}\u{000D}]|(\\([\u{0020}-\u{FFFF}]|[\"\\/bfnrt]))))*\"/As";
    protected const PATTERN_BLOCK_STRING = '/"""("?"?(\\"""|\\(?!=""")|[^"\\]))*"""/As';
    protected const PUNCTUATION          = '!$&:=@|()[]{}';

    /**
     * The lexer owning this token reader.
     *
     * @var LexerInterface
     */
    protected $lexer;

    /**
     * @inheritdoc
     */
    public function setLexer(LexerInterface $lexer)
    {
        $this->lexer = $lexer;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLexer(): LexerInterface
    {
        return $this->lexer;
    }

    /**
     * @inheritdoc
     * @throws SyntaxErrorException
     */
    public function read(string $body, int $pos, int $line, int $col, Token $prev): ?Token
    {
        if (false !== \strpos(self::PUNCTUATION, $body[$pos])) {
            return $this->createPunctuation($body, $pos, $line, $col, $prev);
        }

        if (\preg_match(static::PATTERN_COMMENT, $body, $match, null, $pos)) {
            return $this->createComment($pos, $pos + \mb_strlen($match[0]), $line, $col, $prev, $match[0]);
        }

        if (\preg_match(static::PATTERN_NAME, $body, $match, null, $pos)) {
            return $this->createName($pos, $pos + \mb_strlen($match[0]), $line, $col, $prev, $match[0]);
        }

        if (\preg_match(static::PATTERN_INT, $body, $match, null, $pos)) {
            return $this->createInt($pos, $pos + \mb_strlen($match[0]), $line, $col, $prev, $match[0]);
        }

        if (\preg_match(static::PATTERN_FLOAT, $body, $match, null, $pos)) {
            return $this->createFloat($pos, $pos + \mb_strlen($match[0]), $line, $col, $prev, $match[0]);
        }

        if (\preg_match(static::PATTERN_SPREAD, $body, $match, null, $pos)) {
            return $this->createSpread($pos, $pos + 3, $line, $col, $prev);
        }

        if (\preg_match(static::PATTERN_STRING, $body, $match, null, $pos)) {
            return $this->createString($pos, $pos + \mb_strlen($match[0]), $line, $col, $prev, $match[0]);
        }

        if (\preg_match(static::PATTERN_BLOCK_STRING, $body, $match, null, $pos)) {
            return $this->createBlockString($pos, $pos + \mb_strlen($match[0]), $line, $col, $prev, $match[0]);
        }

        return null;
    }

    /**
     * @param string      $kind
     * @param int         $start
     * @param int         $line
     * @param int         $col
     * @param Token       $prev
     * @param null|string $value
     * @return Token
     */
    protected function createToken(
        string $kind,
        int $start,
        int $end,
        int $line,
        int $col,
        Token $prev,
        ?string $value = null
    ): Token {
        return new Token($kind, $start, $end, $line, $col, $prev, $value);
    }

    /**
     * @param string $body
     * @param int    $pos
     * @param int    $line
     * @param int    $col
     * @param Token  $prev
     * @return Token
     */
    protected function createPunctuation(string $body, int $pos, int $line, int $col, Token $prev): Token
    {
        $code = \ord($body[$pos]);

        switch ($code) {
            case 33: // !
                return $this->createBang($pos, $line, $col, $prev);
            case 36: // $
                return $this->createDollar($pos, $line, $col, $prev);
            case 38: // &
                return $this->createAmp($pos, $line, $col, $prev);
            case 58: // :
                return $this->createColon($pos, $line, $col, $prev);
            case 61: // =
                return $this->createEquals($pos, $line, $col, $prev);
            case 64: // @
                return $this->createAt($pos, $line, $col, $prev);
            case 124: // |
                return $this->createPipe($pos, $line, $col, $prev);
            case 40:
            case 41: // ( or )~
                return $this->createParenthesis($code, $pos, $line, $col, $prev);
            case 91:
            case 93: // [ or ]
                return $this->createBracket($code, $pos, $line, $col, $prev);
            case 123:
            case 125: // { or }
                return $this->createBrace($code, $pos, $line, $col, $prev);
        }
    }

    /**
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createColon(int $pos, int $line, int $col, Token $prev): Token
    {
        return new Token(TokenKindEnum::COLON, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createAmp(int $pos, int $line, int $col, Token $prev): Token
    {
        return new Token(TokenKindEnum::AMP, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createBang(int $pos, int $line, int $col, Token $prev): Token
    {
        return new Token(TokenKindEnum::BANG, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param int   $code
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createBrace(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        return $code === 123
            ? new Token(TokenKindEnum::BRACE_L, $pos, $pos + 1, $line, $col, $prev)
            : new Token(TokenKindEnum::BRACE_R, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param int   $code
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createBracket(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        return $code === 91
            ? new Token(TokenKindEnum::BRACKET_L, $pos, $pos + 1, $line, $col, $prev)
            : new Token(TokenKindEnum::BRACKET_R, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createDollar(int $pos, int $line, int $col, Token $prev): Token
    {
        return new Token(TokenKindEnum::DOLLAR, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createPipe(int $pos, int $line, int $col, Token $prev): Token
    {
        return new Token(TokenKindEnum::PIPE, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param int   $code
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createParenthesis(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        return $code === 40
            ? new Token(TokenKindEnum::PAREN_L, $pos, $pos + 1, $line, $col, $prev)
            : new Token(TokenKindEnum::PAREN_R, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createEquals(int $pos, int $line, int $col, Token $prev): Token
    {
        return new Token(TokenKindEnum::EQUALS, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createAt(int $pos, int $line, int $col, Token $prev): Token
    {
        return new Token(TokenKindEnum::AT, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param array ...$args
     * @return Token
     */
    protected function createComment(...$args): Token
    {
        return $this->createToken(TokenKindEnum::COMMENT, ...$args);
    }

    /**
     * @param array ...$args
     * @return Token
     */
    protected function createName(...$args): Token
    {
        return $this->createToken(TokenKindEnum::NAME, ...$args);
    }

    /**
     * @param array ...$args
     * @return Token
     */
    protected function createInt(...$args): Token
    {
        return $this->createToken(TokenKindEnum::INT, ...$args);
    }

    /**
     * @param array ...$args
     * @return Token
     */
    protected function createFloat(...$args): Token
    {
        return $this->createToken(TokenKindEnum::FLOAT, ...$args);
    }

    /**
     * @param array ...$args
     * @return Token
     */
    protected function createSpread(...$args): Token
    {
        return $this->createToken(TokenKindEnum::SPREAD, ...$args);
    }

    /**
     * @param array ...$args
     * @return Token
     */
    protected function createString(...$args): Token
    {
        return $this->createToken(TokenKindEnum::STRING, ...$args);
    }

    /**
     * @param array ...$args
     * @return Token
     */
    protected function createBlockString(...$args): Token
    {
        return $this->createToken(TokenKindEnum::BLOCK_STRING, ...$args);
    }

//    /**
//     * @param string $body
//     * @param int    $bodyLength
//     * @param int    $pos
//     * @param int    $line
//     * @param int    $col
//     * @param Token  $prev
//     * @return Token
//     * @throws SyntaxErrorException
//     */
//    protected function readBlockString(string $body, int $bodyLength, int $pos, int $line, int $col, Token $prev): Token
//    {
//        $start      = $pos;
//        $pos        = $start + 3;
//        $chunkStart = $pos;
//        $rawValue   = '';
//
//        while ($pos < $bodyLength && ($code = charCodeAt($body, $pos)) !== null) {
//            // Closing Triple-Quote (""")
//            if (isTripleQuote($body, $code, $pos)) {
//                $rawValue .= sliceString($body, $chunkStart, $pos);
//                return new Token(
//                    TokenKindEnum::BLOCK_STRING,
//                    $start,
//                    $pos + 3,
//                    $line,
//                    $col,
//                    $prev,
//                    blockStringValue($rawValue)
//                );
//            }
//
//            if (isSourceCharacter($code) && !isLineTerminator($code)) {
//                throw new SyntaxErrorException(
//                    $this->lexer->getSource(),
//                    $pos,
//                    \sprintf('Invalid character within String: %s.', printCharCode($code))
//                );
//            }
//
//            if (isEscapedTripleQuote($body, $code, $pos)) {
//                $rawValue   .= sliceString($body, $chunkStart, $pos) . '"""';
//                $pos        += 4;
//                $chunkStart = $pos;
//            } else {
//                ++$pos;
//            }
//        }
//
//        throw new SyntaxErrorException($this->lexer->getSource(), $pos, 'Unterminated string.');
//    }
//
//    /**
//     * @param int   $code
//     * @param int   $pos
//     * @param int   $line
//     * @param int   $col
//     * @param Token $prev
//     * @return Token
//     * @throws SyntaxErrorException
//     */
//    protected function readNumber(int $code, int $pos, int $line, int $col, Token $prev): Token
//    {
//        $body    = $this->lexer->getBody();
//        $start   = $pos;
//        $isFloat = false;
//
//        if ($code === 45) {
//            // -
//            $code = charCodeAt($body, ++$pos);
//        }
//
//        if ($code === 48) {
//            // 0
//            $code = charCodeAt($body, ++$pos);
//
//            if (isNumber($code)) {
//                throw new SyntaxErrorException(
//                    $this->lexer->getSource(),
//                    $pos,
//                    \sprintf('Invalid number, unexpected digit after 0: %s.', printCharCode($code))
//                );
//            }
//        } else {
//            $pos  = $this->readDigits($code, $pos);
//            $code = charCodeAt($body, $pos);
//        }
//
//        if ($code === 46) {
//            // .
//            $isFloat = true;
//            $code    = charCodeAt($body, ++$pos);
//            $pos     = $this->readDigits($code, $pos);
//            $code    = charCodeAt($body, $pos);
//        }
//
//        if ($code === 69 || $code === 101) {
//            // e or E
//            $isFloat = true;
//            $code    = charCodeAt($body, ++$pos);
//
//            if ($code === 43 || $code === 45) {
//                // + or -
//                $code = charCodeAt($body, ++$pos);
//            }
//
//            $pos = $this->readDigits($code, $pos);
//        }
//
//        return new Token(
//            $isFloat ? TokenKindEnum::FLOAT : TokenKindEnum::INT,
//            $start,
//            $pos,
//            $line,
//            $col,
//            $prev,
//            sliceString($body, $start, $pos)
//        );
//    }
//
//    /**
//     * @param string $body
//     * @param int    $bodyLength
//     * @param int    $pos
//     * @param int    $line
//     * @param int    $col
//     * @param Token  $prev
//     * @return Token
//     * @throws SyntaxErrorException
//     */
//    protected function readString(string $body, int $bodyLength, int $pos, int $line, int $col, Token $prev): Token
//    {
//        $start      = $pos;
//        $pos        = $start + 1;
//        $chunkStart = $pos;
//        $value      = '';
//
//        while ($pos < $bodyLength && ($code = charCodeAt($body, $pos)) !== null && !isLineTerminator($code)) {
//            // Closing Quote (")
//            if ($code === 34) {
//                $value .= sliceString($body, $chunkStart, $pos);
//                return new Token(TokenKindEnum::STRING, $start, $pos + 1, $line, $col, $prev, $value);
//            }
//
//            if (isSourceCharacter($code)) {
//                throw new SyntaxErrorException(
//                    $this->lexer->getSource(),
//                    $pos,
//                    \sprintf('Invalid character within String: %s.', printCharCode($code))
//                );
//            }
//
//            ++$pos;
//
//            if ($code === 92) {
//                // \
//                $value .= sliceString($body, $chunkStart, $pos - 1);
//                $code  = charCodeAt($body, $pos);
//
//                switch ($code) {
//                    case 34:
//                        $value .= '"';
//                        break;
//                    case 47:
//                        $value .= '/';
//                        break;
//                    case 92:
//                        $value .= '\\';
//                        break;
//                    case 98:
//                        $value .= '\b';
//                        break;
//                    case 102:
//                        $value .= '\f';
//                        break;
//                    case 110:
//                        $value .= '\n';
//                        break;
//                    case 114:
//                        $value .= '\r';
//                        break;
//                    case 116:
//                        $value .= '\t';
//                        break;
//                    case 117:
//                        // u
//                        $unicodeString = sliceString($body, $pos + 1, $pos + 5);
//
//                        if (!\preg_match('/[0-9A-Fa-f]{4}/', $unicodeString)) {
//                            throw new SyntaxErrorException(
//                                $this->lexer->getSource(),
//                                $pos,
//                                \sprintf('Invalid character escape sequence: \\u%s.', $unicodeString)
//                            );
//                        }
//
//                        $value .= '\\u' . $unicodeString;
//                        $pos   += 4;
//                        break;
//                    default:
//                        throw new SyntaxErrorException(
//                            $this->lexer->getSource(),
//                            $pos,
//                            \sprintf('Invalid character escape sequence: \\%s.', \chr($code))
//                        );
//                }
//
//                ++$pos;
//
//                $chunkStart = $pos;
//            }
//        }
//
//        throw new SyntaxErrorException($this->lexer->getSource(), $pos, 'Unterminated string.');
//    }
//
//    /**
//     * @param int   $pos
//     * @param int   $line
//     * @param int   $col
//     * @param Token $prev
//     * @return Token
//     */
//    protected function readComment(int $pos, int $line, int $col, Token $prev): Token
//    {
//        $body  = $this->lexer->getBody();
//        $start = $pos;
//
//        do {
//            $code = charCodeAt($body, ++$pos);
//        } while ($code !== null && ($code > 0x001f || $code === 0x0009)); // SourceCharacter but not LineTerminator
//
//        return new Token(
//            TokenKindEnum::COMMENT,
//            $start,
//            $pos,
//            $line,
//            $col,
//            $prev,
//            sliceString($body, $start + 1, $pos)
//        );
//    }
//
//    /**
//     * @param int $code
//     * @param int $pos
//     * @return int
//     * @throws SyntaxErrorException
//     */
//    protected function readDigits(int $code, int $pos): int
//    {
//        $body = $this->lexer->getBody();
//
//        if (isNumber($code)) {
//            do {
//                $code = charCodeAt($body, ++$pos);
//            } while (isNumber($code));
//
//            return $pos;
//        }
//
//        throw new SyntaxErrorException(
//            $this->lexer->getSource(),
//            $pos,
//            sprintf('Invalid number, expected digit but got: %s.', printCharCode($code))
//        );
//    }
}
