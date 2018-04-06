<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\SchemaValidationException;
use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Schema\Resolver\ResolverRegistryInterface;
use Digia\GraphQL\Schema\SchemaInterface;
use Digia\GraphQL\Util\SerializationInterface;

/**
 * @param string|Source $source
 * @param array|ResolverRegistryInterface $resolverRegistry
 * @param array $options
 *
 * @return SchemaInterface
 * @throws InvariantException
 */
function buildSchema(
    $source,
    $resolverRegistry = [],
    array $options = []
): SchemaInterface {
    return GraphQL::buildSchema($source, $resolverRegistry, $options);
}

/**
 * @param SchemaInterface $schema
 * @param string|Source $source
 * @param array|ResolverRegistryInterface $resolverRegistry
 * @param array $options
 *
 * @return SchemaInterface
 * @throws InvariantException
 */
function extendSchema(
    SchemaInterface $schema,
    $source,
    $resolverRegistry = [],
    array $options = []
): SchemaInterface {
    return GraphQL::extendSchema($schema, $source, $resolverRegistry, $options);
}

/**
 * @param SchemaInterface $schema
 *
 * @return SchemaValidationException[]
 */
function validateSchema(SchemaInterface $schema): array
{
    return GraphQL::validateSchema($schema);
}

/**
 * @param string|Source $source
 * @param array $options
 *
 * @return NodeInterface|DocumentNode|SerializationInterface
 * @throws InvariantException
 * @throws SyntaxErrorException
 */
function parse($source, array $options = []): NodeInterface
{
    return GraphQL::parse($source, $options);
}

/**
 * @param string|Source $source
 * @param array $options
 *
 * @return NodeInterface|SerializationInterface
 * @throws InvariantException
 * @throws SyntaxErrorException
 */
function parseValue($source, array $options = []): NodeInterface
{
    return GraphQL::parseValue($source, $options);
}

/**
 * @param string|Source $source
 * @param array $options
 *
 * @return NodeInterface|SerializationInterface
 * @throws InvariantException
 * @throws SyntaxErrorException
 */
function parseType($source, array $options = []): NodeInterface
{
    return GraphQL::parseType($source, $options);
}

/**
 * @param SchemaInterface $schema
 * @param DocumentNode $document
 *
 * @return array|ValidationException[]
 */
function validate(SchemaInterface $schema, DocumentNode $document): array
{
    return GraphQL::validate($schema, $document);
}

/**
 * @param SchemaInterface $schema
 * @param DocumentNode $document
 * @param mixed|null $rootValue
 * @param mixed|null $contextValue
 * @param array $variableValues
 * @param mixed|null $operationName
 * @param callable|null $fieldResolver
 *
 * @return ExecutionResult
 */
function execute(
    SchemaInterface $schema,
    DocumentNode $document,
    $rootValue = null,
    $contextValue = null,
    array $variableValues = [],
    $operationName = null,
    callable $fieldResolver = null
): ExecutionResult {
    return GraphQL::execute(
        $schema,
        $document,
        $rootValue,
        $contextValue,
        $variableValues,
        $operationName,
        $fieldResolver
    );
}

/**
 * @param NodeInterface $node
 *
 * @return string
 */
function printNode(NodeInterface $node): string
{
    return GraphQL::print($node);
}

/**
 * @param SchemaInterface $schema
 * @param string $source
 * @param mixed|null $rootValue
 * @param mixed|null $contextValue
 * @param array $variableValues
 * @param mixed|null $operationName
 * @param callable|null $fieldResolver
 *
 * @return ExecutionResult
 * @throws InvariantException
 */
function graphql(
    SchemaInterface $schema,
    string $source,
    $rootValue = null,
    $contextValue = null,
    array $variableValues = [],
    $operationName = null,
    callable $fieldResolver = null
): array {
    $schemaValidationErrors = validateSchema($schema);
    if (!empty($schemaValidationErrors)) {
        return (new ExecutionResult([], $schemaValidationErrors))->toArray();
    }

    $document = null;

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
        $fieldResolver
    );

    return $result->toArray();
}
