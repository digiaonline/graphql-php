<?php

namespace Digia\GraphQL\Provider;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\AST\Node\BooleanValueNode;
use Digia\GraphQL\Language\AST\Node\FloatValueNode;
use Digia\GraphQL\Language\AST\Node\IntValueNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use Digia\GraphQL\Type\Definition\TypeNameEnum;
use League\Container\ServiceProvider\AbstractServiceProvider;
use function Digia\GraphQL\Type\GraphQLScalarType;
use function Digia\GraphQL\Util\coerceBoolean;
use function Digia\GraphQL\Util\coerceFloat;
use function Digia\GraphQL\Util\coerceInt;
use function Digia\GraphQL\Util\coerceString;

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
                    return $astNode instanceof BooleanValueNode ? $astNode->getValue() : null;
                },
            ]);
        }, true/* $shared */);

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
                    if ($astNode instanceof FloatValueNode || $astNode instanceof IntValueNode) {
                        return $astNode->getValue();
                    }

                    return null;
                },
            ]);
        }, true/* $shared */);

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
                    return $astNode instanceof IntValueNode ? $astNode->getValue() : null;
                },
            ]);
        }, true/* $shared */);

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
                    if ($astNode instanceof StringValueNode || $astNode instanceof IntValueNode) {
                        return $astNode->getValue();
                    }

                    return null;
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
                    return $astNode instanceof StringValueNode ? $astNode->getValue() : null;
                },
            ]);
        }, true/* $shared */);
    }
}
