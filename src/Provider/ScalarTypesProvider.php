<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\AST\Node\BooleanValueNode;
use Digia\GraphQL\Language\AST\Node\FloatValueNode;
use Digia\GraphQL\Language\AST\Node\IntValueNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Type\Definition\TypeNameEnum;
use const Digia\GraphQL\Type\MAX_INT;
use const Digia\GraphQL\Type\MIN_INT;
use League\Container\ServiceProvider\AbstractServiceProvider;
use function Digia\GraphQL\Type\GraphQLScalarType;

class ScalarTypesProvider extends AbstractServiceProvider
{

    /**
     * @var array
     */
    protected $provides = [
        GraphQL::BOOLEAN,
        GraphQL::FLOAT,
        GraphQL::INT,
        GraphQL::ID,
        GraphQL::STRING,
    ];

    /**
     * @inheritdoc
     */
    public function register()
    {
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

        $this->container->add(GraphQL::BOOLEAN, function () {
            return GraphQLScalarType([
                'name'        => TypeNameEnum::BOOLEAN,
                'description' => 'The `Boolean` scalar type represents `true` or `false`.',
                'serialize'   => function ($value) {
                    return coerceBoolean($value);
                },
                'parseValue'  => function ($value) {
                    return coerceBoolean($value);
                },

                'parseLiteral' => function (NodeInterface $astNode) {
                    /** @var BooleanValueNode $astNode */
                    return $astNode->getKind() === NodeKindEnum::BOOLEAN ? $astNode->getValue() : null;
                },
            ]);
        }, true/* $shared */);

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

        $this->container->add(GraphQL::FLOAT, function () {
            return GraphQLScalarType([
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
                    /** @var FloatValueNode $astNode */
                    return $astNode->getKind() === NodeKindEnum::FLOAT || $astNode->getKind() === NodeKindEnum::INT
                        ? $astNode->getValue()
                        : null;
                },
            ]);
        }, true/* $shared */);

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

        $this->container->add(GraphQL::INT, function () {
            return GraphQLScalarType([
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
                    /** @var IntValueNode $astNode */
                    return $astNode->getKind() === NodeKindEnum::INT ? $astNode->getValue() : null;
                },
            ]);
        }, true/* $shared */);

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

        $this->container->add(GraphQL::ID, function () {
            return GraphQLScalarType([
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
                    /** @var StringValueNode $astNode */
                    return $astNode->getKind() === NodeKindEnum::STRING || $astNode->getKind() === NodeKindEnum::INT
                        ? $astNode->getValue()
                        : null;
                },
            ]);
        }, true/* $shared */);

        $this->container->add(GraphQL::STRING, function () {
            return GraphQLScalarType([
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
                    /** @var StringValueNode $astNode */
                    return $astNode->getKind() === NodeKindEnum::STRING ? $astNode->getValue() : null;
                },
            ]);
        }, true/* $shared */);
    }
}
