<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\GraphQLError;

interface LexerInterface
{

    /**
     * Advances the token stream to the next non-ignored token.
     *
     * @return Token
     * @throws GraphQLError
     */
    public function advance(): Token;

    /**
     * Looks ahead and returns the next non-ignored token, but does not change
     * the Lexer's state.
     *
     * @return Token
     * @throws GraphQLError
     */
    public function lookahead(): Token;

    /**
     * @return string
     */
    public function getBody(): string;

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getOption(string $name, $default = null);

    /**
     * @return string
     */
    public function getTokenKind(): string;

    /**
     * @return string|null
     */
    public function getTokenValue(): ?string;

    /**
     * @return Token
     */
    public function getToken(): Token;

    /**
     * @return Source
     */
    public function getSource(): Source;

    /**
     * @return Token
     */
    public function getLastToken(): Token;

    /**
     * @return mixed
     */
    public function setSource(Source $source);

    /**
     * @return mixed
     */
    public function setOptions(array $options);
}
