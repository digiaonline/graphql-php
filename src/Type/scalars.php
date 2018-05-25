<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Type\Definition\NamedTypeInterface;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return ScalarType
 */
function Boolean(): ScalarType
{
    return GraphQL::make('GraphQLBoolean');
}

/**
 * @return ScalarType
 */
function Float(): ScalarType
{
    return GraphQL::make('GraphQLFloat');
}

/**
 * @return ScalarType
 */
function Int(): ScalarType
{
    return GraphQL::make('GraphQLInt');
}

/**
 * @return ScalarType
 */
function ID(): ScalarType
{
    return GraphQL::make('GraphQLID');
}

/**
 * @return ScalarType
 */
function String(): ScalarType
{
    return GraphQL::make('GraphQLString');
}

/**
 * @return array
 */
function specifiedScalarTypes(): array
{
    return [
        String(),
        Int(),
        Float(),
        Boolean(),
        ID(),
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
