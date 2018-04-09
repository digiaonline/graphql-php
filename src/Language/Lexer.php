<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\LanguageException;
use Digia\GraphQL\Error\SyntaxErrorException;

class Lexer implements LexerInterface
{

    /**
     * @var Source|null
     */
    protected $source;

    /**
     * @var string|null
     */
    protected $body;

    /**
     * @var int
     */
    protected $bodyLength;

    /**
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
     * The token reader.
     *
     * @var TokenReaderInterface
     */
    protected $reader;

    /**
     * Lexer constructor.
     *
     * @param TokenReaderInterface $reader
     */
    public function __construct(TokenReaderInterface $reader)
    {
        $startOfFileToken = new Token(TokenKindEnum::SOF);

        $reader->setLexer($this);

        $this->reader    = $reader;
        $this->lastToken = $startOfFileToken;
        $this->token     = $startOfFileToken;
        $this->line      = 1;
        $this->lineStart = 0;
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
     * @throws LanguageException
     */
    public function getBody(): string
    {
        return $this->getSource()->getBody();
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
     * @throws LanguageException
     */
    public function getSource(): Source
    {
        if ($this->source instanceof Source) {
            return $this->source;
        }

        throw new LanguageException('No source has been set.');
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
    public function setSource(Source $source)
    {
        $this->body       = $source->getBody();
        $this->bodyLength = \strlen($this->body);
        $this->source     = $source;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param Token $prev
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function readToken(Token $prev): Token
    {
        $pos  = $this->positionAfterWhitespace($prev->getEnd());
        $line = $this->line;
        $col  = 1 + $pos - $this->lineStart;

        if ($pos >= $this->bodyLength) {
            return new Token(TokenKindEnum::EOF, $this->bodyLength, $this->bodyLength, $line, $col, $prev);
        }

        $code = charCodeAt($this->body, $pos);

        $token = $this->reader->read($this->body, $this->bodyLength, $code, $pos, $line, $col, $prev);

        if (null !== $token) {
            return $token;
        }

        throw new SyntaxErrorException($this->source, $pos, $this->unexpectedCharacterMessage($code));
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
     * @param int $startPosition
     * @return int
     */
    protected function positionAfterWhitespace(int $startPosition): int
    {
        $pos = $startPosition;

        while ($pos < $this->bodyLength) {
            $code = charCodeAt($this->body, $pos);

            if ($code === 9 || $code === 32 || $code === 44 || $code === 0xfeff) {
                // tab | space | comma | BOM
                ++$pos;
            } elseif ($code === 10) {
                // new line (\n)
                ++$pos;
                ++$this->line;
                $this->lineStart = $pos;
            } elseif ($code === 13) {
                // carriage return (\r)
                if (charCodeAt($this->body, $pos + 1) === 10) {
                    // carriage return and new line (\r\n)
                    $pos += 2;
                } else {
                    ++$pos;
                }
                ++$this->line;
                $this->lineStart = $pos;
            } else {
                break;
            }
        }

        return $pos;
    }
}
