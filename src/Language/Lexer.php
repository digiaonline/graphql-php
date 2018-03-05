<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Error\SyntaxError;
use Digia\GraphQL\Language\Reader\ReaderInterface;

class Lexer implements LexerInterface
{

    /**
     * @var Source|null
     */
    protected $source;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array|ReaderInterface[]
     */
    protected $readers;

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
     * Lexer constructor.
     *
     * @param ReaderInterface[] $readers
     */
    public function __construct(array $readers)
    {
        $startOfFileToken = new Token(TokenKindEnum::SOF);

        foreach ($readers as $reader) {
            $reader->setLexer($this);
        }

        $this->readers   = $readers;
        $this->lastToken = $startOfFileToken;
        $this->token     = $startOfFileToken;
        $this->line      = 1;
        $this->lineStart = 0;
    }

    /**
     * @inheritdoc
     */
    public function advance(): Token
    {
        $this->lastToken = $this->token;

        return $this->token = $this->lookahead();
    }

    /**
     * @inheritdoc
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
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * @inheritdoc
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
     */
    public function getSource(): Source
    {
        if ($this->source instanceof Source) {
            return $this->source;
        }

        throw new \Exception('No source has been set.');
    }

    /**
     * @inheritdoc
     */
    public function getLastToken(): Token
    {
        return $this->lastToken;
    }

    /**
     * @param Source $source
     * @return Lexer
     */
    public function setSource(Source $source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @param array $options
     * @return
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param int   $code
     * @param int   $pos
     * @param int   $line
     * @param int   $col
     * @param Token $prev
     * @return Token
     * @throws SyntaxError
     */
    public function read(int $code, int $pos, int $line, int $col, Token $prev): Token
    {
        if (($reader = $this->getReader($code, $pos)) !== null) {
            return $reader->read($code, $pos, $line, $col, $prev);
        }

        throw new SyntaxError($this->unexpectedCharacterMessage($code));
    }

    /**
     * @param Token $prev
     * @return Token
     * @throws GraphQLError
     */
    protected function readToken(Token $prev): Token
    {
        $body       = $this->source->getBody();
        $bodyLength = mb_strlen($body);

        $pos  = $this->positionAfterWhitespace($body, $prev->getEnd());
        $line = $this->line;
        $col  = 1 + $pos - $this->lineStart;

        if ($pos >= $bodyLength) {
            return new Token(TokenKindEnum::EOF, $bodyLength, $bodyLength, $line, $col, $prev);
        }

        $code = charCodeAt($body, $pos);

        if (isSourceCharacter($code)) {
            throw new SyntaxError(sprintf('Cannot contain the invalid character %s', printCharCode($code)));
        }

        return $this->read($code, $pos, $line, $col, $prev);
    }

    /**
     * @param int $code
     * @return string
     */
    protected function unexpectedCharacterMessage(int $code): string
    {
        if ($code === 39) {
            // '
            return 'Unexpected single quote character (\'), did you mean to use a double quote (")?';
        }

        return sprintf('Cannot parse the unexpected character %s', printCharCode($code));
    }

    /**
     * @param int $code
     * @param int $pos
     * @return ReaderInterface|null
     */
    protected function getReader(int $code, int $pos): ?ReaderInterface
    {
        foreach ($this->readers as $reader) {
            if ($reader instanceof ReaderInterface && $reader->supportsReader($code, $pos)) {
                return $reader;
            }
        }

        return null;
    }

    /**
     * @param string $body
     * @param int    $startPosition
     * @return int
     */
    protected function positionAfterWhitespace(string $body, int $startPosition): int
    {
        $bodyLength = mb_strlen($body);
        $pos        = $startPosition;

        while ($pos < $bodyLength) {
            $code = charCodeAt($body, $pos);

            if ($code === 9 || $code === 32 || $code === 44 || $code === 0xfeff) {
                // tab | space | comma | BOM
                ++$pos;
            } elseif ($code === 10) {
                // new line
                ++$pos;
                $this->advanceLine($pos);
            } elseif ($code === 13) {
                // carriage return
                if (charCodeAt($body, $pos + 1) === 10) {
                    $pos += 2;
                } else {
                    ++$pos;
                }
                $this->advanceLine($pos);
            } else {
                break;
            }
        }

        return $pos;
    }

    /**
     * @param int $pos
     */
    protected function advanceLine(int $pos)
    {
        ++$this->line;
        $this->lineStart = $pos;
    }
}
