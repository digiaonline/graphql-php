<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\GraphQLRuntime;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use function Digia\GraphQL\Util\arraySome;

const MAX_INT = 2147483647;
const MIN_INT = -2147483648;

/**
 * @return ScalarType
 */
function GraphQLBoolean(): ScalarType
{
    return GraphQLRuntime::get()->make('GraphQLBoolean');
}

/**
 * @return ScalarType
 */
function GraphQLFloat(): ScalarType
{
    return GraphQLRuntime::get()->make('GraphQLFloat');
}

/**
 * @return ScalarType
 */
function GraphQLInt(): ScalarType
{
    return GraphQLRuntime::get()->make('GraphQLInt');
}

/**
 * @return ScalarType
 */
function GraphQLID(): ScalarType
{
    return GraphQLRuntime::get()->make('GraphQLID');
}

/**
 * @return ScalarType
 */
function GraphQLString(): ScalarType
{
    return GraphQLRuntime::get()->make('GraphQLString');
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
            return $type->getName() === $specifiedScalarType->getName();
        }
    );
}
