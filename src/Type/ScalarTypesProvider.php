<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Node\BooleanValueNode;
use Digia\GraphQL\Language\Node\FloatValueNode;
use Digia\GraphQL\Language\Node\IntValueNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\StringValueNode;
use Digia\GraphQL\Type\Coercer\BooleanCoercer;
use Digia\GraphQL\Type\Coercer\FloatCoercer;
use Digia\GraphQL\Type\Coercer\IntCoercer;
use Digia\GraphQL\Type\Coercer\StringCoercer;
use Digia\GraphQL\Type\Definition\TypeNameEnum;
use League\Container\ServiceProvider\AbstractServiceProvider;

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
        $this->container
            ->share(GraphQL::BOOLEAN, function (BooleanCoercer $coercer) {
                return newScalarType([
                    'name'         => TypeNameEnum::BOOLEAN,
                    'description'  => 'The `Boolean` scalar type represents `true` or `false`.',
                    'serialize'    => function ($value) use ($coercer) {
                        return $coercer->coerce($value);
                    },
                    'parseValue'   => function ($value) use ($coercer) {
                        return $coercer->coerce($value);
                    },
                    'parseLiteral' => function (NodeInterface $node) {
                        if ($node instanceof BooleanValueNode) {
                            return $node->getValue();
                        }
                        return null;
                    },
                ]);
            })
            ->addArgument(BooleanCoercer::class);

        $this->container
            ->share(GraphQL::FLOAT, function (FloatCoercer $coercer) {
                return newScalarType([
                    'name'         => TypeNameEnum::FLOAT,
                    'description'  =>
                        'The `Float` scalar type represents signed double-precision fractional ' .
                        'values as specified by ' .
                        '[IEEE 754](https://en.wikipedia.org/wiki/IEEE_754).',
                    'serialize'    => function ($value) use ($coercer) {
                        return $coercer->coerce($value);
                    },
                    'parseValue'   => function ($value) use ($coercer) {
                        return $coercer->coerce($value);
                    },
                    'parseLiteral' => function (NodeInterface $node) {
                        if ($node instanceof FloatValueNode || $node instanceof IntValueNode) {
                            return $node->getValue();
                        }
                        return null;
                    },
                ]);
            })
            ->addArgument(FloatCoercer::class);

        $this->container
            ->share(GraphQL::INT, function (IntCoercer $coercer) {
                return newScalarType([
                    'name'         => TypeNameEnum::INT,
                    'description'  =>
                        'The `Int` scalar type represents non-fractional signed whole numeric ' .
                        'values. Int can represent values between -(2^31) and 2^31 - 1.',
                    'serialize'    => function ($value) use ($coercer) {
                        return $coercer->coerce($value);
                    },
                    'parseValue'   => function ($value) use ($coercer) {
                        return $coercer->coerce($value);
                    },
                    'parseLiteral' => function (NodeInterface $node) {
                        if ($node instanceof IntValueNode) {
                            $value = (int)$node->getValue();
                            if ((string)$node->getValue() === (string)$value &&
                                $value <= PHP_INT_MAX && $value >= PHP_INT_MIN) {
                                return $value;
                            }
                        }
                        return null;
                    },
                ]);
            })
            ->addArgument(IntCoercer::class);

        $this->container
            ->share(GraphQL::ID, function (StringCoercer $coercer) {
                return newScalarType([
                    'name'         => TypeNameEnum::ID,
                    'description'  =>
                        'The `ID` scalar type represents a unique identifier, often used to ' .
                        'refetch an object or as key for a cache. The ID type appears in a JSON ' .
                        'response as a String; however, it is not intended to be human-readable. ' .
                        'When expected as an input type, any string (such as `"4"`) or integer ' .
                        '(such as `4`) input value will be accepted as an ID.',
                    'serialize'    => function ($value) use ($coercer) {
                        return $coercer->coerce($value);
                    },
                    'parseValue'   => function ($value) use ($coercer) {
                        return $coercer->coerce($value);
                    },
                    'parseLiteral' => function (NodeInterface $node) {
                        if ($node instanceof StringValueNode || $node instanceof IntValueNode) {
                            return $node->getValue();
                        }
                        return null;
                    },
                ]);
            })
            ->addArgument(StringCoercer::class);

        $this->container
            ->share(GraphQL::STRING, function (StringCoercer $coercer) {
                return newScalarType([
                    'name'         => TypeNameEnum::STRING,
                    'description'  =>
                        'The `String` scalar type represents textual data, represented as UTF-8 ' .
                        'character sequences. The String type is most often used by GraphQL to ' .
                        'represent free-form human-readable text.',
                    'serialize'    => function ($value) use ($coercer) {
                        return $coercer->coerce($value);
                    },
                    'parseValue'   => function ($value) use ($coercer) {
                        return $coercer->coerce($value);
                    },
                    'parseLiteral' => function (NodeInterface $node) {
                        if ($node instanceof StringValueNode) {
                            return $node->getValue();
                        }
                        return null;
                    },
                ]);
            })
            ->addArgument(StringCoercer::class);
    }
}
