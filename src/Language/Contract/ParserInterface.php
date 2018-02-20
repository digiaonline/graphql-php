<?php

namespace Digia\GraphQL\Language\Contract;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\Source;

interface ParserInterface
{

    /**
     * Given a GraphQL source, parses it into a Document.
     * Throws GraphQLError if a syntax error is encountered.
     *
     * @param Source $source
     * @param array  $options
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function parse(Source $source, array $options = []): NodeInterface;

    /**
     * Given a string containing a GraphQL value (ex. `[42]`), parse the AST for
     * that value.
     * Throws GraphQLError if a syntax error is encountered.
     *
     * This is useful within tools that operate upon GraphQL Values directly and
     * in isolation of complete GraphQL documents.
     *
     * @param Source $source
     * @param array  $options
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function parseValue(Source $source, array $options = []): NodeInterface;

    /**
     * Given a string containing a GraphQL Type (ex. `[Int!]`), parse the AST for
     * that type.
     * Throws GraphQLError if a syntax error is encountered.
     *
     * This is useful within tools that operate upon GraphQL Types directly and
     * in isolation of complete GraphQL documents.
     *
     * @param Source $source
     * @param array  $options
     * @return NodeInterface
     * @throws GraphQLError
     */
    public function parseType(Source $source, array $options = []): NodeInterface;
}
