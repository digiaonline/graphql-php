<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\Source;

/**
 * @return GraphQLRuntime
 */
function graphql(): GraphQLRuntime
{
    return GraphQLRuntime::getInstance();
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface
 * @throws GraphQLError
 * @throws \Exception
 */
function parse($source, array $options = []): NodeInterface
{
    return graphql()->getParser()->parse(
        graphql()->getLexer()
            ->setSource($source instanceof Source ? $source : new Source($source))
            ->setOptions($options)
    );
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface
 * @throws GraphQLError
 * @throws \Exception
 */
function parseValue($source, array $options = []): NodeInterface
{
    return graphql()->getParser()->parseValue(
        graphql()->getLexer()
            ->setSource($source instanceof Source ? $source : new Source($source))
            ->setOptions($options)
    );
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface
 * @throws GraphQLError
 * @throws \Exception
 */
function parseType($source, array $options = []): NodeInterface
{
    return graphql()->getParser()->parseType(
        graphql()->getLexer()
            ->setSource($source instanceof Source ? $source : new Source($source))
            ->setOptions($options)
    );
}

/**
 * @param NodeInterface $node
 * @return string
 */
function printNode(NodeInterface $node): string
{
    return graphql()->getPrinter()->print($node);
}
