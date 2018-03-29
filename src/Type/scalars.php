<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\arraySome;

/**
 * @return ScalarType
 */
function GraphQLBoolean(): ScalarType
{
    return GraphQL::make('GraphQLBoolean');
}

/**
 * @return ScalarType
 */
function GraphQLFloat(): ScalarType
{
    return GraphQL::make('GraphQLFloat');
}

/**
 * @return ScalarType
 */
function GraphQLInt(): ScalarType
{
    return GraphQL::make('GraphQLInt');
}

/**
 * @return ScalarType
 */
function GraphQLID(): ScalarType
{
    return GraphQL::make('GraphQLID');
}

/**
 * @return ScalarType
 */
function GraphQLString(): ScalarType
{
    return GraphQL::make('GraphQLString');
}

/**
 * @return array
 */
function specifiedScalarTypes(): array
{
    return [
        GraphQLString(),
        GraphQLInt(),
        GraphQLFloat(),
        GraphQLBoolean(),
        GraphQLID(),
    ];
}

/**
 * @param TypeInterface $type
 * @return bool
 */
function isSpecifiedScalarType(TypeInterface $type): bool
{
    return arraySome(
        specifiedScalarTypes(),
        function (ScalarType $specifiedScalarType) use ($type) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $type->getName() === $specifiedScalarType->getName();
        }
    );
}
