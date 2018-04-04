<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\SyntaxErrorException;

class TokenReader implements TokenReaderInterface
{

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
     */
    public function read(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        switch ($code) {
            case 33: // !
                return $this->readBang($code, $pos, $line, $col, $prev);
            case 35: // #
                return $this->readComment($code, $pos, $line, $col, $prev);
            case 36: // $
                return $this->readDollar($code, $pos, $line, $col, $prev);
            case 38: // &
                return $this->readAmp($code, $pos, $line, $col, $prev);
            case 58: // :
                return $this->readColon($code, $pos, $line, $col, $prev);
            case 61: // =
                return $this->readEquals($code, $pos, $line, $col, $prev);
            case 64: // @
                return $this->readAt($code, $pos, $line, $col, $prev);
            case 124: // |
                return $this->readPipe($code, $pos, $line, $col, $prev);
            case 40:
            case 41: // ( or )~
                return $this->readParenthesis($code, $pos, $line, $col, $prev);
            case 91:
            case 93: // [ or ]
                return $this->readBracket($code, $pos, $line, $col, $prev);
            case 123:
            case 125: // { or }
                return $this->readBrace($code, $pos, $line, $col, $prev);
        }

        // Int:   -?(0|[1-9][0-9]*)
        // Float: -?(0|[1-9][0-9]*)(\.[0-9]+)?((E|e)(+|-)?[0-9]+)?
        if (isNumber($code)) {
            return $this->readNumber($code, $pos, $line, $col, $prev);
        }

        if ($this->isLetter($code) || $this->isUnderscore($code)) {
            return $this->readName($code, $pos, $line, $col, $prev);
        }

        $body = $this->lexer->getBody();

        // Spread: ...
        if ($code === 46 && charCodeAt($body,
            $pos + 1) === 46 && charCodeAt($body, $pos + 2) === 46) {
            return $this->readSpread($code, $pos, $line, $col, $prev);
        }

        // String: "([^"\\\u000A\u000D]|(\\(u[0-9a-fA-F]{4}|["\\/bfnrt])))*"
        if ($code === 34 && charCodeAt($body, $pos + 1) !== 34) {
            return $this->readString($code, $pos, $line, $col, $prev);
        }

        // Block String: """("?"?(\\"""|\\(?!=""")|[^"\\]))*"""
        if ($this->isTripleQuote($body, $code, $pos)) {
            return $this->readBlockString($code, $pos, $line, $col, $prev);
        }
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readName(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        $body = $this->lexer->getBody();
        $bodyLength = mb_strlen($body);
        $start = $pos;
        $pos = $start + 1;

        while ($pos !== $bodyLength && ($code = charCodeAt($body,
            $pos)) !== null && $this->isAlphaNumeric($code)) {
            ++$pos;
        }

        return new Token(TokenKindEnum::NAME, $start, $pos, $line, $col, $prev,
          sliceString($body, $start, $pos));
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function readBlockString(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
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
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function readNumber(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        $body = $this->lexer->getBody();
        $start = $pos;
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
                  sprintf('Invalid number, unexpected digit after 0: %s.',
                    printCharCode($code))
                );
            }
        } else {
            $pos = $this->readDigits($code, $pos);
            $code = charCodeAt($body, $pos);
        }

        if ($code === 46) {
            // .
            $isFloat = true;
            $code = charCodeAt($body, ++$pos);
            $pos = $this->readDigits($code, $pos);
            $code = charCodeAt($body, $pos);
        }

        if ($code === 69 || $code === 101) {
            // e or E
            $isFloat = true;
            $code = charCodeAt($body, ++$pos);

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
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function readString(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
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
                throw new SyntaxErrorException(
                    $this->lexer->getSource(),
                    $pos,
                    sprintf('Invalid character within String: %s', printCharCode($code))
                );
            }
            ++$pos;
            if ($code === 92) {
                // \
                $value .= sliceString($body, $chunkStart, $pos - 1);
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
                        // u
                        $unicodeString = sliceString($body, $pos - 1, $pos + 5);
                        $charCode      = uniCharCode(
                            charCodeAt($body, $pos + 1),
                            charCodeAt($body, $pos + 2),
                            charCodeAt($body, $pos + 3),
                            charCodeAt($body, $pos + 4)
                        );
                        if ($charCode < 0) {
                            throw new SyntaxErrorException(
                                $this->lexer->getSource(),
                                $pos,
                                sprintf(
                                    'Invalid character escape sequence: %s',
                                    $unicodeString
                                )
                            );
                        }
                        $value .= $unicodeString;
                        $pos   += 4;
                        break;
                    default:
                        throw new SyntaxErrorException(
                            $this->lexer->getSource(),
                            $pos,
                            sprintf('Invalid character escape sequence: \\%s', chr($code))
                        );
                }
                ++$pos;
                $chunkStart = $pos;
            }
        }
        throw new SyntaxErrorException($this->lexer->getSource(), $pos - 1, 'Unterminated string.');
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readSpread(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        return new Token(TokenKindEnum::SPREAD, $pos, $pos + 3, $line, $col,
          $prev);
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readDollar(
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
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readPipe(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        return new Token(TokenKindEnum::PIPE, $pos, $pos + 1, $line, $col,
          $prev);
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readParenthesis(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        return $code === 40
          ? new Token(TokenKindEnum::PAREN_L, $pos, $pos + 1, $line, $col,
            $prev)
          : new Token(TokenKindEnum::PAREN_R, $pos, $pos + 1, $line, $col,
            $prev);
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readEquals(
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
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readAt(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        return new Token(TokenKindEnum::AT, $pos, $pos + 1, $line, $col, $prev);
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readComment(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        $body = $this->lexer->getBody();
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
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readColon(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        return new Token(TokenKindEnum::COLON, $pos, $pos + 1, $line, $col,
          $prev);
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readAmp(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        return new Token(TokenKindEnum::AMP, $pos, $pos + 1, $line, $col,
          $prev);
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readBang(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        return new Token(TokenKindEnum::BANG, $pos, $pos + 1, $line, $col,
          $prev);
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readBrace(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        return $code === 123
          ? new Token(TokenKindEnum::BRACE_L, $pos, $pos + 1, $line, $col,
            $prev)
          : new Token(TokenKindEnum::BRACE_R, $pos, $pos + 1, $line, $col,
            $prev);
    }

    /**
     * @param int $code
     * @param int $pos
     * @param int $line
     * @param int $col
     * @param Token $prev
     * @return Token
     */
    protected function readBracket(
      int $code,
      int $pos,
      int $line,
      int $col,
      Token $prev
    ): Token {
        return $code === 91
          ? new Token(TokenKindEnum::BRACKET_L, $pos, $pos + 1, $line, $col,
            $prev)
          : new Token(TokenKindEnum::BRACKET_R, $pos, $pos + 1, $line, $col,
            $prev);
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
          sprintf('Invalid number, expected digit but got: %s',
            printCharCode($code))
        );
    }

    /**
     * @TODO Move to utils.
     *
     * @param int $code
     * @return bool
     */
    protected function isLineTerminator(int $code): bool
    {
        return $code === 0x000a || $code === 0x000d;
    }

    /**
     * @TODO Move to utils.
     *
     * @param int $code
     * @return bool
     */
    protected function isSourceCharacter(int $code): bool
    {
        return $code < 0x0020 && $code !== 0x0009;
    }

    /**
     * @TODO Move to utils.
     *
     * @param string $body
     * @param int $code
     * @param int $pos
     * @return bool
     */
    protected function isTripleQuote(string $body, int $code, int $pos): bool
    {
        return $code === 34 && charCodeAt($body,
            $pos + 1) === 34 && charCodeAt($body, $pos + 2) === 34; // """
    }

    /**
     * @TODO Move to utils.
     *
     * @param string $body
     * @param int $code
     * @param int $pos
     * @return bool
     */
    protected function isEscapedTripleQuote(
      string $body,
      int $code,
      int $pos
    ): bool {
        return $code === 92 &&
          charCodeAt($body, $pos + 1) === 34 &&
          charCodeAt($body, $pos + 2) === 34 &&
          charCodeAt($body, $pos + 3) === 34;
    }

    /**
     * @TODO Move to utils.
     *
     * @param int $code
     * @return bool
     */
    protected function isLetter(int $code): bool
    {
        return ($code >= 65 && $code <= 90) || ($code >= 97 && $code <= 122); // a-z or A-Z
    }

    /**
     * @TODO Move to utils.
     *
     * @param int $code
     * @return bool
     */
    protected function isNumber(int $code): bool
    {
        return $code >= 48 && $code <= 57; // 0-9
    }

    /**
     * @TODO Move to utils.
     *
     * @param int $code
     * @return bool
     */
    protected function isUnderscore(int $code): bool
    {
        return $code === 95; // _
    }

    /**
     * @TODO Move to utils.
     *
     * @param int $code
     * @return bool
     */
    protected function isAlphaNumeric(int $code): bool
    {
        return $this->isLetter($code) || $this->isNumber($code) || $this->isUnderscore($code);
    }
}
