<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\SyntaxErrorException;

class Lexer implements LexerInterface
{
    protected const ENCODING = 'UTF-8';

    /**
     * A map between punctuation character code and the corresponding token kind.
     *
     * @var array
     */
    protected static $codeTokenKindMap = [
        33  => TokenKindEnum::BANG,
        36  => TokenKindEnum::DOLLAR,
        38  => TokenKindEnum::AMP,
        40  => TokenKindEnum::PAREN_L,
        41  => TokenKindEnum::PAREN_R,
        58  => TokenKindEnum::COLON,
        61  => TokenKindEnum::EQUALS,
        64  => TokenKindEnum::AT,
        91  => TokenKindEnum::BRACKET_L,
        93  => TokenKindEnum::BRACKET_R,
        123 => TokenKindEnum::BRACE_L,
        124 => TokenKindEnum::PIPE,
        125 => TokenKindEnum::BRACE_R,
    ];

    /**
     * The source file for this lexer.
     *
     * @var Source
     */
    protected $source;

    /**
     * The contents of the source file.
     *
     * @var string
     */
    protected $body;

    /**
     * The total number of characters in the source file.
     *
     * @var int
     */
    protected $bodyLength;

    /**
     * The options for this lexer.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The previously focused non-ignored token.
     *
     * @var Token
     */
    protected $lastToken;

    /**
     * The currently focused non-ignored token.
     *
     * @var Token
     */
    protected $token;

    /**
     * The current position.
     *
     * @var int
     */
    protected $pos;

    /**
     * The (1-indexed) line containing the current token.
     *
     * @var int
     */
    protected $line;

    /**
     * The character offset at which the current line begins.
     *
     * @var int
     */
    protected $lineStart;

    /**
     * @var array
     */
    protected static $charCodeCache = [];

    /**
     * Lexer constructor.
     * @param Source|null $source
     * @param array       $options
     */
    public function __construct(Source $source, array $options)
    {
        $startOfFileToken = $this->createStartOfFileToken();

        $this->lastToken  = $startOfFileToken;
        $this->token      = $startOfFileToken;
        $this->line       = 1;
        $this->lineStart  = 0;
        $this->body       = $source->getBody();
        $this->bodyLength = \strlen($this->body);
        $this->source     = $source;
        $this->options    = $options;
    }

    /**
     * @inheritdoc
     * @throws SyntaxErrorException
     */
    public function advance(): Token
    {
        $this->lastToken = $this->token;
        return $this->token = $this->lookahead();
    }

    /**
     * @inheritdoc
     * @throws SyntaxErrorException
     */
    public function lookahead(): Token
    {
        $token = $this->token;

        if (TokenKindEnum::EOF !== $token->getKind()) {
            do {
                $next = $this->readToken($token);
                $token->setNext($next);
                $token = $next;
            } while (TokenKindEnum::COMMENT === $token->getKind());
        }

        return $token;
    }

    /**
     * @inheritdoc
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * @inheritdoc
     */
    public function getTokenKind(): string
    {
        return $this->token->getKind();
    }

    /**
     * @inheritdoc
     */
    public function getTokenValue(): ?string
    {
        return $this->token->getValue();
    }

    /**
     * @inheritdoc
     */
    public function getToken(): Token
    {
        return $this->token;
    }

    /**
     * @inheritdoc
     */
    public function getSource(): Source
    {
        return $this->source;
    }

    /**
     * @inheritdoc
     */
    public function getLastToken(): Token
    {
        return $this->lastToken;
    }

    /**
     * @inheritdoc
     */
    public function createSyntaxErrorException(?string $description = null): SyntaxErrorException
    {
        return new SyntaxErrorException(
            $this->source,
            $this->pos,
            $description ?? $this->unexpectedCharacterMessage($this->readCharCode($this->pos))
        );
    }

    /**
     * Reads the token after the given token.
     *
     * @param Token $prev
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function readToken(Token $prev): Token
    {
        $this->pos = $prev->getEnd();

        $this->skipWhitespace();

        $line = $this->line;
        $col  = (1 + $this->pos) - $this->lineStart;

        if ($this->pos >= $this->bodyLength) {
            return $this->createEndOfFileToken($line, $col, $prev);
        }

        $code = $this->readCharCode($this->pos);

        // Punctuation: [!$&:=@|()\[\]{}]{1}
        if (33 === $code || 36 === $code || 38 === $code || 58 === $code || 61 === $code || 64 === $code || 124 === $code ||
            40 === $code || 41 === $code || 91 === $code || 93 === $code || 123 === $code || 125 === $code) {
            return $this->lexPunctuation($code, $line, $col, $prev);
        }

        // Comment: #[\u0009\u0020-\uFFFF]*
        if (35 === $code) {
            return $this->lexComment($line, $col, $prev);
        }

        // Int:   -?(0|[1-9][0-9]*)
        // Float: -?(0|[1-9][0-9]*)(\.[0-9]+)?((E|e)(+|-)?[0-9]+)?
        if (45 === $code || isNumber($code)) {
            return $this->lexNumber($code, $line, $col, $prev);
        }

        // Name: [_A-Za-z][_0-9A-Za-z]*
        if (isAlphaNumeric($code)) {
            return $this->lexName($line, $col, $prev);
        }

        // Spread: ...
        if ($this->bodyLength >= 3 && $this->isSpread($code)) {
            return $this->lexSpread($line, $col, $prev);
        }

        // String: "([^"\\\u000A\u000D]|(\\(u[0-9a-fA-F]{4}|["\\/bfnrt])))*"
        if ($this->isString($code)) {
            return $this->lexString($line, $col, $prev);
        }

        // Block String: """("?"?(\\"""|\\(?!=""")|[^"\\]))*"""
        if ($this->bodyLength >= 3 && $this->isTripleQuote($code)) {
            return $this->lexBlockString($line, $col, $prev);
        }

        throw $this->createSyntaxErrorException();
    }

    /**
     * @return Token
     */
    protected function createStartOfFileToken(): Token
    {
        return new Token(TokenKindEnum::SOF);
    }

    /**
     * Creates an End Of File (EOF) token.
     *
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function createEndOfFileToken(int $line, int $col, Token $prev): Token
    {
        return new Token(TokenKindEnum::EOF, $this->bodyLength, $this->bodyLength, $line, $col, $prev);
    }

    /**
     * Reads a punctuation token from the source file.
     *
     * @param int   $code
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function lexPunctuation(int $code, int $line, int $col, Token $prev): ?Token
    {
        if (!isset(self::$codeTokenKindMap[$code])) {
            throw $this->createSyntaxErrorException();
        }

        return new Token(self::$codeTokenKindMap[$code], $this->pos, $this->pos + 1, $line, $col, $prev);
    }

    /**
     * Reads a name token from the source file.
     *
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function lexName(int $line, int $col, Token $prev): Token
    {
        $start = $this->pos;

        ++$this->pos;

        while ($this->pos !== $this->bodyLength &&
            ($code = $this->readCharCode($this->pos)) !== null &&
            isAlphaNumeric($code)) {
            ++$this->pos;
        }

        $value = sliceString($this->body, $start, $this->pos);

        return new Token(TokenKindEnum::NAME, $start, $this->pos, $line, $col, $prev, $value);
    }

    /**
     * Reads a number (int or float) token from the source file.
     *
     * @param int   $code
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function lexNumber(int $code, int $line, int $col, Token $prev): Token
    {
        $start   = $this->pos;
        $isFloat = false;

        if (45 === $code) {
            // -
            $code = $this->readCharCode(++$this->pos);
        }

        if (48 === $code) {
            // 0
            $code = $this->readCharCode(++$this->pos);

            if (isNumber($code)) {
                throw $this->createSyntaxErrorException(
                    \sprintf('Invalid number, unexpected digit after 0: %s.', printCharCode($code))
                );
            }
        } else {
            $this->skipDigits($code);
            $code = $this->readCharCode($this->pos);
        }

        if (46 === $code) {
            // .
            $isFloat = true;

            $code = $this->readCharCode(++$this->pos);
            $this->skipDigits($code);
            $code = $this->readCharCode($this->pos);
        }

        if (69 === $code || 101 === $code) {
            // e or E
            $isFloat = true;

            $code = $this->readCharCode(++$this->pos);

            if (43 === $code || 45 === $code) {
                // + or -
                $code = $this->readCharCode(++$this->pos);
            }

            $this->skipDigits($code);
        }

        return new Token(
            $isFloat ? TokenKindEnum::FLOAT : TokenKindEnum::INT,
            $start,
            $this->pos,
            $line,
            $col,
            $prev,
            sliceString($this->body, $start, $this->pos)
        );
    }

    /**
     * Skips digits at the current position.
     *
     * @param int $code
     * @throws SyntaxErrorException
     */
    protected function skipDigits(int $code): void
    {
        if (isNumber($code)) {
            do {
                $code = $this->readCharCode(++$this->pos);
            } while (isNumber($code));

            return;
        }

        throw $this->createSyntaxErrorException(
            \sprintf('Invalid number, expected digit but got: %s.', printCharCode($code))
        );
    }

    /**
     * Reads a comment token from the source file.
     *
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function lexComment(int $line, int $col, Token $prev): Token
    {
        $start = $this->pos;

        do {
            $code = $this->readCharCode(++$this->pos);
        } while ($code !== null && ($code > 0x001f || 0x0009 === $code)); // SourceCharacter but not LineTerminator

        return new Token(
            TokenKindEnum::COMMENT,
            $start,
            $this->pos,
            $line,
            $col,
            $prev,
            sliceString($this->body, $start + 1, $this->pos)
        );
    }

    /**
     * Reads a spread token from the source.
     *
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     */
    protected function lexSpread(int $line, int $col, Token $prev): Token
    {
        return new Token(TokenKindEnum::SPREAD, $this->pos, $this->pos + 3, $line, $col, $prev);
    }

    /**
     * Reads a string token from the source.
     *
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function lexString(int $line, int $col, Token $prev): Token
    {
        $start      = $this->pos;
        $chunkStart = ++$this->pos; // skip the quote
        $value      = '';

        while ($this->pos < $this->bodyLength &&
            ($code = $this->readCharCode($this->pos)) !== null && !isLineTerminator($code)) {
            // Closing Quote (")
            if (34 === $code) {
                $value .= sliceString($this->body, $chunkStart, $this->pos);
                return new Token(TokenKindEnum::STRING, $start, $this->pos + 1, $line, $col, $prev, $value);
            }

            if (isSourceCharacter($code)) {
                throw $this->createSyntaxErrorException(
                    \sprintf('Invalid character within String: %s.', printCharCode($code))
                );
            }

            ++$this->pos;

            if (92 === $code) {
                // \
                $value .= sliceString($this->body, $chunkStart, $this->pos - 1);

                $code = $this->readCharCode($this->pos);

                switch ($code) {
                    case 34: // "
                        $value .= '"';
                        break;
                    case 47: // /
                        $value .= '/';
                        break;
                    case 92: // \
                        $value .= '\\';
                        break;
                    case 98: // b
                        $value .= '\b';
                        break;
                    case 102: // f
                        $value .= '\f';
                        break;
                    case 110: // n
                        $value .= '\n';
                        break;
                    case 114: // r
                        $value .= '\r';
                        break;
                    case 116: // t
                        $value .= '\t';
                        break;
                    case 117: // u
                        $unicodeString = sliceString($this->body, $this->pos + 1, $this->pos + 5);

                        if (!\preg_match('/[0-9A-Fa-f]{4}/', $unicodeString)) {
                            throw $this->createSyntaxErrorException(
                                \sprintf('Invalid character escape sequence: \\u%s.', $unicodeString)
                            );
                        }

                        $value     .= '\\u' . $unicodeString;
                        $this->pos += 4;

                        break;
                    default:
                        throw $this->createSyntaxErrorException(
                            \sprintf('Invalid character escape sequence: \\%s.', \chr($code))
                        );
                }

                ++$this->pos;

                $chunkStart = $this->pos;
            }
        }

        throw $this->createSyntaxErrorException('Unterminated string.');
    }

    /**
     * Reads a block string token from the source file.
     *
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function lexBlockString(int $line, int $col, Token $prev): Token
    {
        $start      = $this->pos;
        $this->pos  = $start + 3; // skip the triple-quote
        $chunkStart = $this->pos;
        $rawValue   = '';

        while ($this->pos < $this->bodyLength && ($code = $this->readCharCode($this->pos)) !== null) {
            // Closing Triple-Quote (""")
            if ($this->isTripleQuote($code)) {
                $rawValue .= sliceString($this->body, $chunkStart, $this->pos);
                return new Token(
                    TokenKindEnum::BLOCK_STRING,
                    $start,
                    $this->pos + 3,
                    $line,
                    $col,
                    $prev,
                    blockStringValue($rawValue)
                );
            }

            if (isSourceCharacter($code) && !isLineTerminator($code)) {
                throw $this->createSyntaxErrorException(
                    \sprintf('Invalid character within String: %s.', printCharCode($code))
                );
            }

            if ($this->isEscapedTripleQuote($code)) {
                $rawValue   .= sliceString($this->body, $chunkStart, $this->pos) . '"""';
                $this->pos  += 4;
                $chunkStart = $this->pos;
            } else {
                ++$this->pos;
            }
        }

        throw $this->createSyntaxErrorException('Unterminated string.');
    }

    /**
     * Skips whitespace at the current position.
     */
    protected function skipWhitespace(): void
    {
        while ($this->pos < $this->bodyLength) {
            $code = $this->readCharCode($this->pos);

            if (9 === $code || 32 === $code || 44 === $code || 0xfeff === $code) {
                // tab | space | comma | BOM
                ++$this->pos;
            } elseif (10 === $code) {
                // new line (\n)
                ++$this->pos;
                ++$this->line;
                $this->lineStart = $this->pos;
            } elseif (13 === $code) {
                // carriage return (\r)
                if (10 === $this->readCharCode($this->pos + 1)) {
                    // carriage return and new line (\r\n)
                    $this->pos += 2;
                } else {
                    ++$this->pos;
                }
                ++$this->line;
                $this->lineStart = $this->pos;
            } else {
                break;
            }
        }
    }

    /**
     * @param int $pos
     * @return int
     */
    protected function readCharCode(int $pos): int
    {
        $char = \mb_substr($this->body, $pos, 1, self::ENCODING);

        if ('' === $char) {
            return 0;
        }

        if (!isset(self::$charCodeCache[$char])) {
            self::$charCodeCache[$char] = \mb_ord($char, self::ENCODING);
        }

        return self::$charCodeCache[$char];
    }

    /**
     * Report a message that an unexpected character was encountered.
     *
     * @param int $code
     * @return string
     */
    protected function unexpectedCharacterMessage(int $code): string
    {
        if (isSourceCharacter($code) && !isLineTerminator($code)) {
            return \sprintf('Cannot contain the invalid character %s.', printCharCode($code));
        }

        if ($code === 39) {
            // '
            return 'Unexpected single quote character (\'), did you mean to use a double quote (")?';
        }

        return \sprintf('Cannot parse the unexpected character %s.', printCharCode($code));
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function isSpread(int $code): bool
    {
        return 46 === $code &&
            $this->readCharCode($this->pos + 1) === 46 &&
            $this->readCharCode($this->pos + 2) === 46; // ...
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function isString(int $code): bool
    {
        return 34 === $code && $this->readCharCode($this->pos + 1) !== 34;
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function isTripleQuote(int $code): bool
    {
        return 34 === $code &&
            34 === $this->readCharCode($this->pos + 1) &&
            34 === $this->readCharCode($this->pos + 2); // """
    }

    /**
     * @param int $code
     * @return bool
     */
    protected function isEscapedTripleQuote(int $code): bool
    {
        return $code === 92 &&
            34 === $this->readCharCode($this->pos + 1) &&
            34 === $this->readCharCode($this->pos + 2) &&
            34 === $this->readCharCode($this->pos + 3); // \"""
    }
}
