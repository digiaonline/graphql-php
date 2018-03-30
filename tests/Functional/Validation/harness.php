<?php

namespace Digia\GraphQL\Test\Functional\Validation;

use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Schema\SchemaInterface;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\GraphQLDirective;
use function Digia\GraphQL\Type\GraphQLEnumType;
use function Digia\GraphQL\Type\GraphQLFloat;
use function Digia\GraphQL\Type\GraphQLID;
use function Digia\GraphQL\Type\GraphQLInputObjectType;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLScalarType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\GraphQLUnionType;

function Being(): InterfaceType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLInterfaceType([
            'name'   => 'Being',
            'fields' => function () {
                return [
                    'name' => [
                        'type' => GraphQLString(),
                        'args' => ['surname' => ['type' => GraphQLBoolean()]],
                    ],
                ];
            },
        ]);
}

function Pet(): InterfaceType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLInterfaceType([
            'name'   => 'Pet',
            'fields' => function () {
                return [
                    'name' => [
                        'type' => GraphQLString(),
                        'args' => ['surname' => ['type' => GraphQLBoolean()]],
                    ],
                ];
            },
        ]);
}

function Canine(): InterfaceType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLInterfaceType([
            'name'   => 'Canine',
            'fields' => function () {
                return [
                    'name' => [
                        'type' => GraphQLString(),
                        'args' => ['surname' => ['type' => GraphQLBoolean()]],
                    ],
                ];
            },
        ]);
}

function DogCommand(): EnumType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLEnumType([
            'name'   => 'DogCommand',
            'values' => [
                'SIT'  => ['value' => 0],
                'HEEL' => ['value' => 1],
                'DOWN' => ['value' => 2],
            ],
        ]);
}

function Dog(): ObjectType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'       => 'Dog',
            'fields'     => function () {
                return [
                    'name'            => [
                        'type' => GraphQLString(),
                        'args' => ['surname' => ['type' => GraphQLBoolean()]],
                    ],
                    'nickname'        => ['type' => GraphQLString()],
                    'barkVolume'      => ['type' => GraphQLInt()],
                    'barks'           => ['type' => GraphQLBoolean()],
                    'doesKnowCommand' => [
                        'type' => GraphQLBoolean(),
                        'args' => [
                            'dogCommand' => ['type' => DogCommand()],
                        ],
                    ],
                    'isHouseTrained'  => [
                        'type' => GraphQLBoolean(),
                        'args' => [
                            'atOtherHomes' => [
                                'type'         => GraphQLBoolean(),
                                'defaultValue' => true,
                            ],
                        ],
                    ],
                    'isAtLocation'    => [
                        'type' => GraphQLBoolean(),
                        'args' => ['x' => ['type' => GraphQLInt()], 'y' => ['type' => GraphQLInt()]],
                    ],
                ];
            },
            'interfaces' => [Being(), Pet(), Canine()],
        ]);
}

function Cat(): ObjectType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'       => 'Cat',
            'fields'     => function () {
                return [
                    'name'       => [
                        'type' => GraphQLString(),
                        'args' => ['surname' => ['type' => GraphQLBoolean()]],
                    ],
                    'nickname'   => ['type' => GraphQLString()],
                    'meows'      => ['type' => GraphQLBoolean()],
                    'meowVolume' => ['type' => GraphQLInt()],
                    'furColor'   => ['type' => FurColor()],
                ];
            },
            'interfaces' => [Being(), Pet()],
        ]);
}

function CatOrDog(): UnionType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLUnionType([
            'name'  => 'CatOrDog',
            'types' => [Cat(), Dog()],
        ]);
}

function Intelligent(): InterfaceType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLInterfaceType([
            'name'   => 'Intelligent',
            'fields' => [
                'iq' => ['type' => GraphQLInt()],
            ],
        ]);
}

function Human(): ObjectType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'       => 'Human',
            'interfaces' => [Being(), Intelligent()],
            'fields'     => function () {
                return [
                    'name'      => [
                        'type' => GraphQLString(),
                        'args' => ['surname' => ['type' => GraphQLBoolean()]],
                    ],
                    'pets'      => ['type' => GraphQLList(Pet())],
                    'relatives' => ['type' => GraphQLList(Human())],
                    'iq'        => ['type' => GraphQLInt()],
                ];
            },
        ]);
}

function Alien(): ObjectType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'       => 'Alien',
            'interfaces' => [Being(), Intelligent()],
            'fields'     => function () {
                return [
                    'iq'      => ['type' => GraphQLInt()],
                    'name'    => [
                        'type' => GraphQLString(),
                        'args' => ['surname' => ['type' => GraphQLBoolean()]],
                    ],
                    'numEyes' => ['type' => GraphQLInt()],
                ];
            },
        ]);
}

function DogOrHuman(): UnionType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLUnionType([
            'name'  => 'DogOrHuman',
            'types' => [Dog(), Human()],
        ]);
}

function HumanOrAlien(): UnionType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLUnionType([
            'name'  => 'HumanOrAlien',
            'types' => [Human(), Alien()],
        ]);
}

function FurColor(): EnumType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLEnumType([
            'name'   => 'FurColor',
            'values' => [
                'BROWN'   => ['value' => 0],
                'BLACK'   => ['value' => 1],
                'TAN'     => ['value' => 2],
                'SPOTTED' => ['value' => 3],
                'NO_FUR'  => ['value' => 4],
                'UNKNOWN' => ['value' => 5],
            ],
        ]);
}

function ComplexInput(): InputObjectType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLInputObjectType([
            'name'   => 'ComplexInput',
            'fields' => [
                'requiredField'   => ['type' => GraphQLNonNull(GraphQLBoolean())],
                'intField'        => ['type' => GraphQLInt()],
                'stringField'     => ['type' => GraphQLString()],
                'booleanField'    => ['type' => GraphQLBoolean()],
                'stringListField' => ['type' => GraphQLList(GraphQLString())],
            ],
        ]);
}

function ComplicatedArgs(): ObjectType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'   => 'ComplicatedArgs',
            // TODO List
            // TODO Coercion
            // TODO NotNulls
            'fields' => function () {
                return [
                    'intArgField'               => [
                        'type' => GraphQLString(),
                        'args' => ['intArg' => ['type' => GraphQLInt()]],
                    ],
                    'nonNullIntArgField'        => [
                        'type' => GraphQLString(),
                        'args' => ['nonNullIntArg' => ['type' => GraphQLNonNull(GraphQLInt())]],
                    ],
                    'stringArgField'            => [
                        'type' => GraphQLString(),
                        'args' => ['stringArg' => ['type' => GraphQLString()]],
                    ],
                    'booleanArgField'           => [
                        'type' => GraphQLString(),
                        'args' => ['booleanArg' => ['type' => GraphQLBoolean()]],
                    ],
                    'enumArgField'              => [
                        'type' => GraphQLString(),
                        'args' => ['enumArg' => ['type' => FurColor()]],
                    ],
                    'floatArgField'             => [
                        'type' => GraphQLString(),
                        'args' => ['floatArg' => ['type' => GraphQLFloat()]],
                    ],
                    'idArgField'                => [
                        'type' => GraphQLString(),
                        'args' => ['idArg' => ['type' => GraphQLID()]],
                    ],
                    'stringListArgField'        => [
                        'type' => GraphQLString(),
                        'args' => ['stringListArg' => ['type' => GraphQLList(GraphQLString())]],
                    ],
                    'stringListNonNullArgField' => [
                        'type' => GraphQLString(),
                        'args' => ['stringListNonNullArg' => ['type' => GraphQLList(GraphQLNonNull(GraphQLString()))]],
                    ],
                    'complexArgField'           => [
                        'type' => GraphQLString(),
                        'args' => ['complexArg' => ['type' => ComplexInput()]],
                    ],
                    'multipleReqs'              => [
                        'type' => GraphQLString(),
                        'args' => [
                            'req1' => ['type' => GraphQLNonNull(GraphQLInt())],
                            'req2' => ['type' => GraphQLNonNull(GraphQLInt())],
                        ],
                    ],
                    'multipleOpts'              => [
                        'type' => GraphQLString(),
                        'args' => [
                            'opt1' => ['type' => GraphQLInt(), 'defaultValue' => 0],
                            'opt2' => ['type' => GraphQLInt(), 'defaultValue' => 0],
                        ],
                    ],
                    'multipleOptsAndReq'        => [
                        'type' => GraphQLString(),
                        'args' => [
                            'req1' => ['type' => GraphQLNonNull(GraphQLInt())],
                            'req2' => ['type' => GraphQLNonNull(GraphQLInt())],
                            'opt1' => ['type' => GraphQLInt(), 'defaultValue' => 0],
                            'opt2' => ['type' => GraphQLInt(), 'defaultValue' => 0],
                        ],
                    ],
                ];
            },
        ]);
}

function InvalidScalar(): ScalarType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLScalarType([
            'name'         => 'Invalid',
            'serialize'    => function ($value) {
                return $value;
            },
            'parseLiteral' => function ($node) {
                throw new \Exception(sprintf('Invalid scalar is always invalid: %s', $node->getValue()));
            },
            'parseValue'   => function ($value) {
                throw new \Exception(sprintf('Invalid scalar is always invalid: %s', $value));
            },
        ]);
}

function AnyScalar(): ScalarType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLScalarType([
            'name'         => 'Any',
            'serialize'    => function ($value) {
                return $value;
            },
            'parseLiteral' => function ($node) {
                return $node;
            },
            'parseValue'   => function ($value) {
                return $value;
            },
        ]);
}

function QueryRoot(): ObjectType
{
    static $instance = null;
    return $instance ??
        $instance = GraphQLObjectType([
            'name'   => 'QueryRoot',
            'fields' => function () {
                return [
                    'human'           => [
                        'args' => ['id' => ['type' => GraphQLID()]],
                        'type' => Human(),
                    ],
                    'alien'           => ['type' => Alien()],
                    'dog'             => ['type' => Dog()],
                    'cat'             => ['type' => Cat()],
                    'pet'             => ['type' => Pet()],
                    'catOrDog'        => ['type' => CatOrDog()],
                    'dogOrHuman'      => ['type' => DogOrHuman()],
                    'humanOrAlien'    => ['type' => HumanOrAlien()],
                    'complicatedArgs' => ['type' => ComplicatedArgs()],
                    'invalidArg'      => [
                        'args' => ['arg' => ['type' => InvalidScalar()]],
                        'type' => GraphQLString(),
                    ],
                    'anyArg'          => [
                        'args' => ['arg' => ['type' => AnyScalar()]],
                        'type' => GraphQLString(),
                    ],
                ];
            },
        ]);
}

/**
 * @return SchemaInterface
 */
function testSchema(): SchemaInterface
{
    return GraphQLSchema([
        'query'      => QueryRoot(),
        'types'      => [Cat(), Dog(), Human(), Alien()],
        'directives' => [
            GraphQLIncludeDirective(),
            GraphQLSkipDirective(),
            GraphQLDirective([
                'name'      => 'onQuery',
                'locations' => ['QUERY'],
            ]),
            GraphQLDirective([
                'name'      => 'onMutation',
                'locations' => ['MUTATION'],
            ]),
            GraphQLDirective([
                'name'      => 'onSubscription',
                'locations' => ['SUBSCRIPTION'],
            ]),
            GraphQLDirective([
                'name'      => 'onField',
                'locations' => ['FIELD'],
            ]),
            GraphQLDirective([
                'name'      => 'onFragmentDefinition',
                'locations' => ['FRAGMENT_DEFINITION'],
            ]),
            GraphQLDirective([
                'name'      => 'onFragmentSpread',
                'locations' => ['FRAGMENT_SPREAD'],
            ]),
            GraphQLDirective([
                'name'      => 'onInlineFragment',
                'locations' => ['INLINE_FRAGMENT'],
            ]),
            GraphQLDirective([
                'name'      => 'onSchema',
                'locations' => ['SCHEMA'],
            ]),
            GraphQLDirective([
                'name'      => 'onScalar',
                'locations' => ['SCALAR'],
            ]),
            GraphQLDirective([
                'name'      => 'onObject',
                'locations' => ['OBJECT'],
            ]),
            GraphQLDirective([
                'name'      => 'onFieldDefinition',
                'locations' => ['FIELD_DEFINITION'],
            ]),
            GraphQLDirective([
                'name'      => 'onArgumentDefinition',
                'locations' => ['ARGUMENT_DEFINITION'],
            ]),
            GraphQLDirective([
                'name'      => 'onInterface',
                'locations' => ['INTERFACE'],
            ]),
            GraphQLDirective([
                'name'      => 'onUnion',
                'locations' => ['UNION'],
            ]),
            GraphQLDirective([
                'name'      => 'onEnum',
                'locations' => ['ENUM'],
            ]),
            GraphQLDirective([
                'name'      => 'onEnumValue',
                'locations' => ['ENUM_VALUE'],
            ]),
            GraphQLDirective([
                'name'      => 'onInputObject',
                'locations' => ['INPUT_OBJECT'],
            ]),
            GraphQLDirective([
                'name'      => 'onInputFieldDefinition',
                'locations' => ['INPUT_FIELD_DEFINITION'],
            ]),
        ],
    ]);
}
