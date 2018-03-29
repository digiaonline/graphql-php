<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\SyntaxErrorException;

trait LexerUtilsTrait
{
    /**
     * Determines if the next token is of a given kind.
     *
     * @param LexerInterface $lexer
     * @param string         $kind
     * @return bool
     */
    protected function peek(LexerInterface $lexer, string $kind): bool
    {
        return $lexer->getTokenKind() === $kind;
    }

    /**
     * If the next token is of the given kind, return true after advancing
     * the lexer. Otherwise, do not change the parser state and return false.
     *
     * @param LexerInterface $lexer
     * @param string         $kind
     * @return bool
     */
    protected function skip(LexerInterface $lexer, string $kind): bool
    {
        if ($match = $this->peek($lexer, $kind)) {
            $lexer->advance();
        }

        return $match;
    }

    /**
     * If the next token is of the given kind, return that token after advancing
     * the lexer. Otherwise, do not change the parser state and throw an error.
     *
     * @param LexerInterface $lexer
     * @param string         $kind
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function expect(LexerInterface $lexer, string $kind): Token
    {
        $token = $lexer->getToken();

        if ($token->getKind() === $kind) {
            $lexer->advance();
            return $token;
        }

        throw new SyntaxErrorException(
            $lexer->getSource(),
            $token->getStart(),
            sprintf('Expected %s, found %s.', $kind, $token)
        );
    }

    /**
     * @param LexerInterface $lexer
     * @param string         $value
     * @return Token
     * @throws SyntaxErrorException
     */
    protected function expectKeyword(LexerInterface $lexer, string $value): Token
    {
        $token = $lexer->getToken();

        if ($token->getKind() === TokenKindEnum::NAME && $token->getValue() === $value) {
            $lexer->advance();
            return $token;
        }

        throw new SyntaxErrorException(
            $lexer->getSource(),
            $token->getStart(),
            sprintf('Expected %s, found %s', $value, $token)
        );
    }

    /**
     * Helper function for creating an error when an unexpected lexed token
     * is encountered.
     *
     * @param LexerInterface $lexer
     * @param Token|null     $atToken
     * @return SyntaxErrorException
     */
    protected function unexpected(LexerInterface $lexer, ?Token $atToken = null): SyntaxErrorException
    {
        $token = $atToken ?: $lexer->getToken();

        return new SyntaxErrorException(
            $lexer->getSource(),
            $token->getStart(),
            sprintf('Unexpected %s', $token)
        );
    }

    /**
     * Returns a possibly empty list of parse nodes, determined by
     * the parseFn. This list begins with a lex token of openKind
     * and ends with a lex token of closeKind. Advances the parser
     * to the next lex token after the closing token.
     *
     * @param LexerInterface $lexer
     * @param string         $openKind
     * @param callable       $parseFunction
     * @param string         $closeKind
     * @return array
     * @throws SyntaxErrorException
     */
    protected function any(LexerInterface $lexer, string $openKind, callable $parseFunction, string $closeKind): array
    {
        $this->expect($lexer, $openKind);

        $nodes = [];

        while (!$this->skip($lexer, $closeKind)) {
            $nodes[] = $parseFunction($lexer);
        }

        return $nodes;
    }

    /**
     * Returns a non-empty list of parse nodes, determined by
     * the parseFn. This list begins with a lex token of openKind
     * and ends with a lex token of closeKind. Advances the parser
     * to the next lex token after the closing token.
     *
     * @param LexerInterface $lexer
     * @param string         $openKind
     * @param callable       $parseFunction
     * @param string         $closeKind
     * @return array
     * @throws SyntaxErrorException
     */
    protected function many(LexerInterface $lexer, string $openKind, callable $parseFunction, string $closeKind): array
    {
        $this->expect($lexer, $openKind);

        $nodes = [$parseFunction($lexer)];

        while (!$this->skip($lexer, $closeKind)) {
            $nodes[] = $parseFunction($lexer);
        }

        return $nodes;
    }

    /**
     * @param LexerInterface $lexer
     * @return bool
     */
    protected function peekDescription(LexerInterface $lexer): bool
    {
        return $this->peek($lexer, TokenKindEnum::STRING) || $this->peek($lexer, TokenKindEnum::BLOCK_STRING);
    }

    /**
     * @param LexerInterface $lexer
     * @param Token          $startToken
     * @return array|null
     */
    protected function buildLocation(LexerInterface $lexer, Token $startToken): ?array
    {
        return !$lexer->getOption('noLocation', false) ? [
            'start'  => $startToken->getStart(),
            'end'    => $lexer->getLastToken()->getEnd(),
            'source' => $lexer->getSource(),
        ] : null;
    }
}
