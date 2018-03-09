<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Execution\Execution;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Schema\SchemaBuilderInterface;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\ParserInterface;
use Digia\GraphQL\Language\PrinterInterface;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\SerializationInterface;

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface|DocumentNode|SerializationInterface
 * @throws \Exception
 */
function parse($source, array $options = []): NodeInterface
{
    return GraphQL::get(ParserInterface::class)->parse(
        GraphQL::get(LexerInterface::class)
            ->setSource($source instanceof Source ? $source : new Source($source))
            ->setOptions($options)
    );
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface|SerializationInterface
 * @throws \Exception
 */
function parseValue($source, array $options = []): NodeInterface
{
    return GraphQL::get(ParserInterface::class)->parseValue(
        GraphQL::get(LexerInterface::class)
            ->setSource($source instanceof Source ? $source : new Source($source))
            ->setOptions($options)
    );
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface|SerializationInterface
 * @throws \Exception
 */
function parseType($source, array $options = []): NodeInterface
{
    return GraphQL::get(ParserInterface::class)->parseType(
        GraphQL::get(LexerInterface::class)
            ->setSource($source instanceof Source ? $source : new Source($source))
            ->setOptions($options)
    );
}

/**
 * @param string $source
 * @param array  $options
 * @return SchemaInterface
 * @throws \Digia\GraphQL\Error\ExecutionException
 * @throws \Exception
 */
function buildSchema(string $source, array $options = []): SchemaInterface
{
    return GraphQL::get(SchemaBuilderInterface::class)->build(parse($source, $options));
}

/**
 * @param NodeInterface $node
 * @return string
 */
function printNode(NodeInterface $node): string
{
    return GraphQL::getInstance()->get(PrinterInterface::class)->print($node);
}

/**
 * @param SchemaInterface $schema
 * @param string          $source
 * @param null            $rootValue
 * @param null            $contextValue
 * @param null            $variableValues
 * @param null            $operationName
 * @param callable|null   $fieldResolver
 * @return ExecutionResult
 * @throws Error\ExecutionException
 * @throws \Exception
 */
function graphql(
    SchemaInterface $schema,
    string $source,
    $rootValue = null,
    $contextValue = null,
    $variableValues = null,
    $operationName = null,
    callable $fieldResolver = null
): ExecutionResult {
    /** @noinspection PhpParamsInspection */
    return Execution::execute(
        $schema,
        parse($source),
        $rootValue,
        $contextValue,
        $variableValues,
        $operationName,
        $fieldResolver
    );
}
