<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\ParserInterface;
use Digia\GraphQL\Language\PrinterInterface;
use Digia\GraphQL\Language\Source;

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface
 * @throws GraphQLError
 * @throws \Exception
 */
function parse($source, array $options = []): NodeInterface
{
    return GraphQLRuntime::get(ParserInterface::class)->parse(
        GraphQLRuntime::get(LexerInterface::class)
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
    return GraphQLRuntime::get(ParserInterface::class)->parseValue(
        GraphQLRuntime::get(LexerInterface::class)
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
    return GraphQLRuntime::get(ParserInterface::class)->parseType(
        GraphQLRuntime::get(LexerInterface::class)
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
    return GraphQLRuntime::getInstance()->get(PrinterInterface::class)->print($node);
}
