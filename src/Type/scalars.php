<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\TypeNameEnum;
use function Digia\GraphQL\Util\arraySome;

const MAX_INT = 2147483647;
const MIN_INT = -2147483648;

/**
 * @param $value
 * @return bool
 * @throws \TypeError
 */
function coerceBoolean($value): bool
{
    if (!is_scalar($value)) {
        throw new \TypeError(sprintf('Boolean cannot represent a non-scalar value: %s', $value));
    }

    return (bool)$value;
}

/**
 * @return ScalarType
 */
function GraphQLBoolean(): ScalarType
{
    static $instance = null;

    if (!$instance) {
        $instance = GraphQLScalarType([
            'name'         => TypeNameEnum::BOOLEAN,
            'description'  => 'The `Boolean` scalar type represents `true` or `false`.',
            'serialize'    => function ($value) {
                return coerceBoolean($value);
            },
            'parseValue'   => function ($value) {
                return coerceBoolean($value);
            },
            'parseLiteral' => function (NodeInterface $astNode) {
                return $astNode->getKind() === NodeKindEnum::BOOLEAN ? $astNode->getValue() : null;
            },
        ]);
    }

    return $instance;
}

/**
 * @param $value
 * @return float
 * @throws \TypeError
 */
function coerceFloat($value): float
{
    if ($value === '') {
        throw new \TypeError('Float cannot represent non numeric value: (empty string)');
    }

    if (is_numeric($value) || \is_bool($value)) {
        return (float)$value;
    }

    throw new \TypeError(sprintf('Float cannot represent non numeric value: %s', $value));
}

/**
 * @return ScalarType
 */
function GraphQLFloat(): ScalarType
{
    static $instance = null;

    if (!$instance) {
        $instance = GraphQLScalarType([
            'name'         => TypeNameEnum::FLOAT,
            'description'  =>
                'The `Float` scalar type represents signed double-precision fractional ' .
                'values as specified by ' .
                '[IEEE 754](http://en.wikipedia.org/wiki/IEEE_floating_point).',
            'serialize'    => function ($value) {
                return coerceFloat($value);
            },
            'parseValue'   => function ($value) {
                return coerceFloat($value);
            },
            'parseLiteral' => function (NodeInterface $astNode) {
                return in_array($astNode->getKind(),
                    [NodeKindEnum::FLOAT, NodeKindEnum::INT]) ? $astNode->getValue() : null;
            },
        ]);
    }

    return $instance;
}

/**
 * @param $value
 * @return int
 * @throws \TypeError
 */
function coerceInt($value)
{
    if ($value === '') {
        throw new \TypeError('Int cannot represent non 32-bit signed integer value: (empty string)');
    }

    if (\is_bool($value)) {
        $value = (int)$value;
    }

    if (!\is_int($value) || $value > MAX_INT || $value < MIN_INT) {
        throw new \TypeError(sprintf('Int cannot represent non 32-bit signed integer value: %s', $value));
    }

    $intValue   = (int)$value;
    $floatValue = (float)$value;

    if ($floatValue != $intValue || floor($floatValue) !== $floatValue) {
        throw new \TypeError(sprintf('Int cannot represent non-integer value: %s', $value));
    }

    return $intValue;
}

/**
 * @return ScalarType
 */
function GraphQLInt(): ScalarType
{
    static $instance = null;

    if (!$instance) {
        $instance = GraphQLScalarType([
            'name'         => TypeNameEnum::INT,
            'description'  =>
                'The `Int` scalar type represents non-fractional signed whole numeric ' .
                'values. Int can represent values between -(2^31) and 2^31 - 1.',
            'serialize'    => function ($value) {
                return coerceInt($value);
            },
            'parseValue'   => function ($value) {
                return coerceInt($value);
            },
            'parseLiteral' => function (NodeInterface $astNode) {
                return $astNode->getKind() === NodeKindEnum::INT ? $astNode->getValue() : null;
            },
        ]);
    }

    return $instance;
}

/**
 * @param $value
 * @return string
 * @throws \TypeError
 */
function coerceString($value): string
{
    if ($value === null) {
        return 'null';
    }

    if ($value === true) {
        return 'true';
    }

    if ($value === false) {
        return 'false';
    }

    if (!is_scalar($value)) {
        throw new \TypeError('String cannot represent a non-scalar value');
    }

    return (string)$value;
}

/**
 * @return ScalarType
 */
function GraphQLID(): ScalarType
{
    static $instance = null;

    if (!$instance) {
        $instance = GraphQLScalarType([
            'name'         => TypeNameEnum::ID,
            'description'  =>
                'The `ID` scalar type represents a unique identifier, often used to ' .
                'refetch an object or as key for a cache. The ID type appears in a JSON ' .
                'response as a String; however, it is not intended to be human-readable. ' .
                'When expected as an input type, any string (such as `"4"`) or integer ' .
                '(such as `4`) input value will be accepted as an ID.',
            'serialize'    => function ($value) {
                return coerceString($value);
            },
            'parseValue'   => function ($value) {
                return coerceString($value);
            },
            'parseLiteral' => function (NodeInterface $astNode) {
                return in_array($astNode->getKind(),
                    [NodeKindEnum::STRING, NodeKindEnum::INT]) ? $astNode->getValue() : null;
            },
        ]);
    }

    return $instance;
}

/**
 * @return ScalarType
 */
function GraphQLString(): ScalarType
{
    static $instance = null;

    if (!$instance) {
        $instance = GraphQLScalarType([
            'name'         => TypeNameEnum::STRING,
            'description'  =>
                'The `String` scalar type represents textual data, represented as UTF-8 ' .
                'character sequences. The String type is most often used by GraphQL to ' .
                'represent free-form human-readable text.',
            'serialize'    => function ($value) {
                return coerceString($value);
            },
            'parseValue'   => function ($value) {
                return coerceString($value);
            },
            'parseLiteral' => function (NodeInterface $astNode) {
                return $astNode->getKind() === NodeKindEnum::STRING ? $astNode->getValue() : null;
            },
        ]);
    }

    return $instance;
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
