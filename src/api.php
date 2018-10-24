<?php

namespace Digia\GraphQL;

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
 * @param Schema                              $schema
 * @param DocumentNode                        $document
 * @param mixed|null                          $rootValue
 * @param mixed|null                          $contextValue
 * @param array                               $variableValues
 * @param mixed|null                          $operationName
 * @param callable|null                       $fieldResolver
 * @param ErrorHandlerInterface|callable|null $errorHandler
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
 * @param mixed|null                          $rootValue
 * @param mixed|null                          $contextValue
 * @param array                               $variableValues
 * @param mixed|null                          $operationName
 * @param callable|null                       $fieldResolver
 * @param ErrorHandlerInterface|callable|null $errorHandler
 * @return array
 * @throws InvariantException
 */
function graphql(
    Schema $schema,
    string $source,
    $rootValue = null,
    $contextValue = null,
    array $variableValues = [],
    $operationName = null,
    callable $fieldResolver = null,
    $errorHandler = null
): array {
    $schemaValidationErrors = validateSchema($schema);
    if (!empty($schemaValidationErrors)) {
        return (new ExecutionResult([], $schemaValidationErrors))->toArray();
    }

    try {
        $document = parse($source);
    } catch (SyntaxErrorException $error) {
        return (new ExecutionResult([], [$error]))->toArray();
    }

    $validationErrors = validate($schema, $document);
    if (!empty($validationErrors)) {
        return (new ExecutionResult([], $validationErrors))->toArray();
    }

    $result = execute(
        $schema,
        $document,
        $rootValue,
        $contextValue,
        $variableValues,
        $operationName,
        $fieldResolver,
        $errorHandler
    );

    return $result->toArray();
}
