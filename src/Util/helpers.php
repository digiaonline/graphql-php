<?php

namespace Digia\GraphQL\Util;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Language\Node\ValueNodeInterface;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Type\Definition\TypeInterface;

/**
 * @param string $name
 * @param mixed  $node
 * @return ValidationException|null
 */
function isValidNameError(string $name, $node = null): ?ValidationException
{
    return GraphQL::make(NameHelper::class)->isValidError($name, $node);
}

/**
 * @param TypeInterface $typeA
 * @param TypeInterface $typeB
 * @return bool
 */
function isEqualType(TypeInterface $typeA, TypeInterface $typeB): bool
{
    return GraphQL::make(TypeHelper::class)->isEqualType($typeA, $typeB);
}

/**
 * @param Schema        $schema
 * @param TypeInterface $maybeSubtype
 * @param TypeInterface $superType
 * @return bool
 */
function isTypeSubtypeOf(Schema $schema, TypeInterface $maybeSubtype, TypeInterface $superType): bool
{
    return GraphQL::make(TypeHelper::class)->isTypeSubtypeOf($schema, $maybeSubtype, $superType);
}

/**
 * @param Schema        $schema
 * @param TypeInterface $typeA
 * @param TypeInterface $typeB
 * @return bool
 */
function doTypesOverlap(Schema $schema, TypeInterface $typeA, TypeInterface $typeB): bool
{
    return GraphQL::make(TypeHelper::class)->doTypesOverlap($schema, $typeA, $typeB);
}

/**
 * @param Schema            $schema
 * @param TypeNodeInterface $typeNode
 * @return TypeInterface|null
 */
function typeFromAST(Schema $schema, TypeNodeInterface $typeNode): ?TypeInterface
{
    return GraphQL::make(TypeASTConverter::class)->convert($schema, $typeNode);
}

/**
 * @param NodeInterface|null $node
 * @param TypeInterface      $type
 * @param array              $variables
 * @return mixed
 */
function valueFromAST(?NodeInterface $node, TypeInterface $type, array $variables = [])
{
    return GraphQL::make(ValueASTConverter::class)->convert($node, $type, $variables);
}

/**
 * @param mixed         $value
 * @param TypeInterface $type
 * @return ValueNodeInterface|null
 */
function astFromValue($value, TypeInterface $type): ?ValueNodeInterface
{
    return GraphQL::make(ValueConverter::class)->convert($value, $type);
}
