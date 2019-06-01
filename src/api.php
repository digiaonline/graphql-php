<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Error\Handler\CallableMiddleware;
use Digia\GraphQL\Error\Handler\ErrorHandler;
use Digia\GraphQL\Error\Handler\ErrorHandlerInterface;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Language\Node\ValueNodeInterface;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Language\StringSourceBuilder;
use Digia\GraphQL\Language\SyntaxErrorException;
use Digia\GraphQL\Schema\Resolver\ResolverRegistryInterface;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Schema\Validation\SchemaValidationException;
use Digia\GraphQL\Validation\ValidationException;
use React\Promise\PromiseInterface;

/**
 * @param string|Source                   $source
 * @param array|ResolverRegistryInterface $resolverRegistry
 * @param array                           $options
 * @return Schema
 * @throws InvariantException
 */
function buildSchema($source, $resolverRegistry = [], array $options = []): Schema
{
    if (\is_string($source)) {
        $source = (new StringSourceBuilder($source))->build();
    }

    return GraphQL::buildSchema($source, $resolverRegistry, $options);
}

/**
 * @param Schema                          $schema
 * @param string|Source                   $source
 * @param array|ResolverRegistryInterface $resolverRegistry
 * @param array                           $options
 * @return Schema
 * @throws InvariantException
 */
function extendSchema(Schema $schema, $source, $resolverRegistry = [], array $options = []): Schema
{
    if (\is_string($source)) {
        $source = (new StringSourceBuilder($source))->build();
    }

    return GraphQL::extendSchema($schema, $source, $resolverRegistry, $options);
}

/**
 * @param Schema $schema
 * @return SchemaValidationException[]
 */
function validateSchema(Schema $schema): array
{
    return GraphQL::validateSchema($schema);
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return DocumentNode
 * @throws InvariantException
 * @throws SyntaxErrorException
 */
function parse($source, array $options = []): DocumentNode
{
    if (\is_string($source)) {
        $source = (new StringSourceBuilder($source))->build();
    }

    return GraphQL::parse($source, $options);
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return ValueNodeInterface
 * @throws InvariantException
 * @throws SyntaxErrorException
 */
function parseValue($source, array $options = []): ValueNodeInterface
{
    if (\is_string($source)) {
        $source = (new StringSourceBuilder($source))->build();
    }

    return GraphQL::parseValue($source, $options);
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return TypeNodeInterface
 * @throws InvariantException
 * @throws SyntaxErrorException
 */
function parseType($source, array $options = []): TypeNodeInterface
{
    if (\is_string($source)) {
        $source = (new StringSourceBuilder($source))->build();
    }

    return GraphQL::parseType($source, $options);
}

/**
 * @param Schema       $schema
 * @param DocumentNode $document
 * @return array|ValidationException[]
 */
function validate(Schema $schema, DocumentNode $document): array
{
    return GraphQL::validate($schema, $document);
}

/**
 * @param Schema                     $schema
 * @param DocumentNode               $document
 * @param mixed|null                 $rootValue
 * @param mixed|null                 $contextValue
 * @param array                      $variableValues
 * @param mixed|null                 $operationName
 * @param callable|null              $fieldResolver
 * @param ErrorHandlerInterface|null $errorHandler
 * @return ExecutionResult
 */
function execute(
    Schema $schema,
    DocumentNode $document,
    $rootValue = null,
    $contextValue = null,
    array $variableValues = [],
    $operationName = null,
    callable $fieldResolver = null,
    $errorHandler = null
): ExecutionResult {
    $resultPromise = GraphQL::execute(
        $schema,
        $document,
        $rootValue,
        $contextValue,
        $variableValues,
        $operationName,
        $fieldResolver,
        $errorHandler
    );

    $data = null;
    $resultPromise->then(function (ExecutionResult $result) use (&$data) {
        $data = $result;
    });
    if ($data === null) {
        $data = new ExecutionResult(null, [
            new GraphQLException('It\'s looks like you are using Event Loop. Please use `executeAsync` method instead.')
        ]);
    }
    return $data;
}

/**
 * @param Schema                     $schema
 * @param DocumentNode               $document
 * @param mixed|null                 $rootValue
 * @param mixed|null                 $contextValue
 * @param array                      $variableValues
 * @param mixed|null                 $operationName
 * @param callable|null              $fieldResolver
 * @param ErrorHandlerInterface|null $errorHandler
 * @return PromiseInterface
 */
function executeAsync(
    Schema $schema,
    DocumentNode $document,
    $rootValue = null,
    $contextValue = null,
    array $variableValues = [],
    $operationName = null,
    callable $fieldResolver = null,
    $errorHandler = null
): PromiseInterface {
    return GraphQL::execute(
        $schema,
        $document,
        $rootValue,
        $contextValue,
        $variableValues,
        $operationName,
        $fieldResolver,
        $errorHandler
    );
}

/**
 * @param NodeInterface $node
 * @return string
 */
function printNode(NodeInterface $node): string
{
    return GraphQL::print($node);
}

/**
 * @param Schema                              $schema
 * @param string                              $source
 * @param mixed                               $rootValue
 * @param mixed                               $contextValue
 * @param array                               $variableValues
 * @param string|null                         $operationName
 * @param callable|null                       $fieldResolver
 * @param ErrorHandlerInterface|callable|null $errorHandler
 * @return array
 * @throws InvariantException
 * @throws SyntaxErrorException
 */
function graphql(
    Schema $schema,
    string $source,
    $rootValue = null,
    $contextValue = null,
    array $variableValues = [],
    ?string $operationName = null,
    ?callable $fieldResolver = null,
    $errorHandler = null
): array {
    if (\is_callable($errorHandler)) {
        $errorHandler = new ErrorHandler([new CallableMiddleware($errorHandler)]);
    }

    $resultPromise = GraphQL::process(
        $schema,
        $source,
        $rootValue,
        $contextValue,
        $variableValues,
        $operationName,
        $fieldResolver,
        $errorHandler
    );

    $data = null;
    $resultPromise->then(function (ExecutionResult $result) use (&$data) {
        $data = $result;
    });
    if ($data === null) {
        $data = new ExecutionResult(null, [
            new GraphQLException('It\'s looks like you are using Event Loop. Please use `graphqlAsync` method instead.')
        ]);
    }

    return $data->toArray();
}

/**
 * @param Schema                              $schema
 * @param string                              $source
 * @param mixed                               $rootValue
 * @param mixed                               $contextValue
 * @param array                               $variableValues
 * @param string|null                         $operationName
 * @param callable|null                       $fieldResolver
 * @param ErrorHandlerInterface|callable|null $errorHandler
 * @return PromiseInterface
 * @throws InvariantException
 * @throws SyntaxErrorException
 */
function graphqlAsync(
    Schema $schema,
    string $source,
    $rootValue = null,
    $contextValue = null,
    array $variableValues = [],
    ?string $operationName = null,
    ?callable $fieldResolver = null,
    $errorHandler = null
): PromiseInterface {
    if (\is_callable($errorHandler)) {
        $errorHandler = new ErrorHandler([new CallableMiddleware($errorHandler)]);
    }

    $resultPromise = GraphQL::process(
        $schema,
        $source,
        $rootValue,
        $contextValue,
        $variableValues,
        $operationName,
        $fieldResolver,
        $errorHandler
    );

    return $resultPromise->then(function (ExecutionResult $result) {
        return $result->toArray();
    });
}
