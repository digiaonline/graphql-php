<?php

namespace Digia\GraphQL\Language;

use Digia\GraphQL\Language\Node\NodeInterface;

interface ParserInterface
{

    /**
     * Given a GraphQL source, parses it into a Document.
     *
     * @param Source|string $source
     * @param array         $options
     * @return NodeInterface
     */
    public function parse($source, array $options = []): NodeInterface;

    /**
     * Given a string containing a GraphQL value (ex. `[42]`), parse the AST for
     * that value.
     *
     * This is useful within tools that operate upon GraphQL Values directly and
     * in isolation of complete GraphQL documents.
     *
     * @param Source|string $source
     * @param array         $options
     * @return NodeInterface
     */
    public function parseValue($source, array $options = []): NodeInterface;

    /**
     * Given a string containing a GraphQL Type (ex. `[Int!]`), parse the AST for
     * that type.
     *
     * This is useful within tools that operate upon GraphQL Types directly and
     * in isolation of complete GraphQL documents.
     *
     * @param Source|string $source
     * @param array         $options
     * @return NodeInterface
     */
    public function parseType($source, array $options = []): NodeInterface;
}
