<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Schema\SchemaInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;

/**
 * @param string $name
 *
 * @return string
 */
function assertInvalidName(string $name): string
{
    return GraphQL::make(NameHelper::class)->assertInvalid($name);
}

/**
 * @param string $name
 * @param null $node
 *
 * @return ValidationException|null
 */
function isValidNameError(string $name, $node = null): ?ValidationException
{
    return GraphQL::make(NameHelper::class)->isValidError($name, $node);
}

/**
 * @param TypeInterface $typeA
 * @param TypeInterface $typeB
 *
 * @return bool
 */
function isEqualType(TypeInterface $typeA, TypeInterface $typeB): bool
{
    return GraphQL::make(TypeHelper::class)->isEqualType($typeA, $typeB);
}

/**
 * @param SchemaInterface $schema
 * @param TypeInterface $maybeSubtype
 * @param TypeInterface $superType
 *
 * @return bool
 */
function isTypeSubtypeOf(
    SchemaInterface $schema,
    TypeInterface $maybeSubtype,
    TypeInterface $superType
): bool {
    return GraphQL::make(TypeHelper::class)
        ->isTypeSubtypeOf($schema, $maybeSubtype, $superType);
}

/**
 * @param SchemaInterface $schema
 * @param TypeInterface $typeA
 * @param TypeInterface $typeB
 *
 * @return bool
 */
function doTypesOverlap(
    SchemaInterface $schema,
    TypeInterface $typeA,
    TypeInterface $typeB
): bool {
    return GraphQL::make(TypeHelper::class)
        ->doTypesOverlap($schema, $typeA, $typeB);
}

/**
 * @param SchemaInterface $schema
 * @param TypeNodeInterface $typeNode
 *
 * @return TypeInterface|null
 */
function typeFromAST(
    SchemaInterface $schema,
    TypeNodeInterface $typeNode
): ?TypeInterface
{
    return GraphQL::make(TypeHelper::class)->fromAST($schema, $typeNode);
}

/**
 * @param NodeInterface|null $node
 * @param TypeInterface $type
 * @param array $variables
 *
 * @return mixed
 */
function valueFromAST(
    ?NodeInterface $node,
    TypeInterface $type,
    array $variables = []
) {
    return GraphQL::make(ValueHelper::class)->fromAST($node, $type, $variables);
}
