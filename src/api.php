<?php

namespace Digia\GraphQL;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Execution\ExecutionInterface;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\ParserInterface;
use Digia\GraphQL\Language\PrinterInterface;
use Digia\GraphQL\Language\SchemaBuilder\SchemaBuilderInterface;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\SchemaValidator\SchemaValidatorInterface;
use Digia\GraphQL\Type\SchemaInterface;
use Digia\GraphQL\Util\SerializationInterface;
use Digia\GraphQL\Validation\ValidatorInterface;

/**
 * @param string $source
 * @param array  $options
 * @return SchemaInterface
 * @throws InvariantException
 */
function buildSchema(string $source, array $options = []): SchemaInterface
{
    return GraphQL::get(SchemaBuilderInterface::class)->build(parse($source, $options));
}

/**
 * @param SchemaInterface $schema
 * @return ValidationException[]
 */
function validateSchema(SchemaInterface $schema): array
{
    return GraphQL::get(SchemaValidatorInterface::class)->validate($schema);
}

/**
 * @param string|Source $source
 * @param array         $options
 * @return NodeInterface|DocumentNode|SerializationInterface
 * @throws InvariantException
 * @throws SyntaxErrorException
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
 * @throws InvariantException
 * @throws SyntaxErrorException
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
 * @throws InvariantException
 * @throws SyntaxErrorException
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
 * @param SchemaInterface $schema
 * @param DocumentNode    $document
 * @return array|ValidationException[]
 */
function validate(SchemaInterface $schema, DocumentNode $document): array
{
    return GraphQL::get(ValidatorInterface::class)->validate($schema, $document);
}

/**
 * @param SchemaInterface $schema
 * @param DocumentNode    $document
 * @param mixed|null      $rootValue
 * @param mixed|null      $contextValue
 * @param array           $variableValues
 * @param mixed|null      $operationName
 * @param callable|null   $fieldResolver
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
    return GraphQL::get(ExecutionInterface::class)
        ->execute(
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
 * @return string
 */
function printNode(NodeInterface $node): string
{
    return GraphQL::get(PrinterInterface::class)->print($node);
}

/**
 * @param SchemaInterface $schema
 * @param string          $source
 * @param mixed|null      $rootValue
 * @param mixed|null      $contextValue
 * @param array           $variableValues
 * @param mixed|null      $operationName
 * @param callable|null   $fieldResolver
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
): ExecutionResult {
    $schemaValidationErrors = validateSchema($schema);
    if (!empty($schemaValidationErrors)) {
        return new ExecutionResult([], $schemaValidationErrors);
    }

    $document = null;

    try {
        $document = parse($source);
    } catch (SyntaxErrorException $error) {
        return new ExecutionResult([], [$error]);
    }

    $validationErrors = validate($schema, $document);
    if (!empty($validationErrors)) {
        return new ExecutionResult([], $validationErrors);
    }

    return execute(
        $schema,
        $document,
        $rootValue,
        $contextValue,
        $variableValues,
        $operationName,
        $fieldResolver
    );
}
