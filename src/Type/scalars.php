<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ScalarType;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return ScalarType
 */
function booleanType(): ScalarType
{
    return GraphQL::make(GraphQL::BOOLEAN);
}

/**
 * @return ScalarType
 */
function floatType(): ScalarType
{
    return GraphQL::make(GraphQL::FLOAT);
}

/**
 * @return ScalarType
 */
function intType(): ScalarType
{
    return GraphQL::make(GraphQL::INT);
}

/**
 * @return ScalarType
 */
function idType(): ScalarType
{
    return GraphQL::make(GraphQL::ID);
}

/**
 * @return ScalarType
 */
function stringType(): ScalarType
{
    return GraphQL::make(GraphQL::STRING);
}

/**
 * @return array
 */
function specifiedScalarTypes(): array
{
    return [
        stringType(),
        intType(),
        floatType(),
        booleanType(),
        idType(),
    ];
}

/**
 * @param NamedTypeInterface $type
 * @return bool
 */
function isSpecifiedScalarType(NamedTypeInterface $type): bool
{
    return arraySome(
        specifiedScalarTypes(),
        function (ScalarType $specifiedScalarType) use ($type) {
            return $type->getName() === $specifiedScalarType->getName();
        }
    );
}
