<?php

namespace Digia\GraphQL\Language;

interface LexerInterface
{
    /**
     * Advances the token stream to the next non-ignored token.
     *
     * @return Token
     */
    public function advance(): Token;

    /**
     * Looks ahead and returns the next non-ignored token, but does not change
     * the Lexer's state.
     *
     * @return Token
     */
    public function lookahead(): Token;

    /**
     * Returns an option given to this Lexer by its name.
     *
     * @param string     $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getOption(string $name, $default = null);

    /**
     * Returns the source lexed by this Lexer.
     *
     * @return Source
     */
    public function getSource(): Source;

    /**
     * Returns the token at the Lexer's current position.
     *
     * @return Token
     */
    public function getToken(): Token;

    /**
     * Returns the previous focused token for this Lexer.
     *
     * @return Token
     */
    public function getLastToken(): Token;

    /**
     * Creates a `SyntaxErrorException` for the current position in the source file.
     *
     * @param null|string $description
     * @return SyntaxErrorException
     */
    public function createSyntaxErrorException(?string $description = null): SyntaxErrorException;
}
