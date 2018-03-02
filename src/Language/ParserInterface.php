<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

interface ParserInterface
{

    /**
     * Given a GraphQL source, parses it into a Document.
     * Throws GraphQLError if a syntax error is encountered.
     *
     * @param LexerInterface $lexer
     * @return NodeInterface
     */
    public function parse(LexerInterface $lexer): NodeInterface;

    /**
     * Given a string containing a GraphQL value (ex. `[42]`), parse the AST for
     * that value.
     * Throws GraphQLError if a syntax error is encountered.
     * This is useful within tools that operate upon GraphQL Values directly and
     * in isolation of complete GraphQL documents.
     *
     * @param LexerInterface $lexer
     * @return NodeInterface
     */
    public function parseValue(LexerInterface $lexer): NodeInterface;

    /**
     * Given a string containing a GraphQL Type (ex. `[Int!]`), parse the AST for
     * that type.
     * Throws GraphQLError if a syntax error is encountered.
     * This is useful within tools that operate upon GraphQL Types directly and
     * in isolation of complete GraphQL documents.
     *
     * @param LexerInterface $lexer
     * @return NodeInterface
     */
    public function parseType(LexerInterface $lexer): NodeInterface;
}
