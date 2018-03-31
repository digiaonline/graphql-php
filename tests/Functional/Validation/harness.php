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
use function Digia\GraphQL\Type\newGraphQLDirective;
use function Digia\GraphQL\Type\newGraphQLEnumType;
use function Digia\GraphQL\Type\GraphQLFloat;
use function Digia\GraphQL\Type\GraphQLID;
use function Digia\GraphQL\Type\newGraphQLInputObjectType;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\newGraphQLInterfaceType;
use function Digia\GraphQL\Type\newGraphQLList;
use function Digia\GraphQL\Type\newGraphQLNonNull;
use function Digia\GraphQL\Type\newGraphQLObjectType;
use function Digia\GraphQL\Type\newGraphQLScalarType;
use function Digia\GraphQL\Type\newGraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\newGraphQLUnionType;

function Being(): InterfaceType
{
    static $instance = null;
    return $instance ??
        $instance = newGraphQLInterfaceType([
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
        $instance = newGraphQLInterfaceType([
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
        $instance = newGraphQLInterfaceType([
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
        $instance = newGraphQLEnumType([
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
        $instance = newGraphQLObjectType([
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
        $instance = newGraphQLObjectType([
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
        $instance = newGraphQLUnionType([
            'name'  => 'CatOrDog',
            'types' => [Cat(), Dog()],
        ]);
}

function Intelligent(): InterfaceType
{
    static $instance = null;
    return $instance ??
        $instance = newGraphQLInterfaceType([
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
        $instance = newGraphQLObjectType([
            'name'       => 'Human',
            'interfaces' => [Being(), Intelligent()],
            'fields'     => function () {
                return [
                    'name'      => [
                        'type' => GraphQLString(),
                        'args' => ['surname' => ['type' => GraphQLBoolean()]],
                    ],
                    'pets'      => ['type' => newGraphQLList(Pet())],
                    'relatives' => ['type' => newGraphQLList(Human())],
                    'iq'        => ['type' => GraphQLInt()],
                ];
            },
        ]);
}

function Alien(): ObjectType
{
    static $instance = null;
    return $instance ??
        $instance = newGraphQLObjectType([
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
        $instance = newGraphQLUnionType([
            'name'  => 'DogOrHuman',
            'types' => [Dog(), Human()],
        ]);
}

function HumanOrAlien(): UnionType
{
    static $instance = null;
    return $instance ??
        $instance = newGraphQLUnionType([
            'name'  => 'HumanOrAlien',
            'types' => [Human(), Alien()],
        ]);
}

function FurColor(): EnumType
{
    static $instance = null;
    return $instance ??
        $instance = newGraphQLEnumType([
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
        $instance = newGraphQLInputObjectType([
            'name'   => 'ComplexInput',
            'fields' => [
                'requiredField'   => ['type' => newGraphQLNonNull(GraphQLBoolean())],
                'intField'        => ['type' => GraphQLInt()],
                'stringField'     => ['type' => GraphQLString()],
                'booleanField'    => ['type' => GraphQLBoolean()],
                'stringListField' => ['type' => newGraphQLList(GraphQLString())],
            ],
        ]);
}

function ComplicatedArgs(): ObjectType
{
    static $instance = null;
    return $instance ??
        $instance = newGraphQLObjectType([
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
                        'args' => ['nonNullIntArg' => ['type' => newGraphQLNonNull(GraphQLInt())]],
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
                        'args' => ['stringListArg' => ['type' => newGraphQLList(GraphQLString())]],
                    ],
                    'stringListNonNullArgField' => [
                        'type' => GraphQLString(),
                        'args' => ['stringListNonNullArg' => ['type' => newGraphQLList(newGraphQLNonNull(GraphQLString()))]],
                    ],
                    'complexArgField'           => [
                        'type' => GraphQLString(),
                        'args' => ['complexArg' => ['type' => ComplexInput()]],
                    ],
                    'multipleReqs'              => [
                        'type' => GraphQLString(),
                        'args' => [
                            'req1' => ['type' => newGraphQLNonNull(GraphQLInt())],
                            'req2' => ['type' => newGraphQLNonNull(GraphQLInt())],
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
                            'req1' => ['type' => newGraphQLNonNull(GraphQLInt())],
                            'req2' => ['type' => newGraphQLNonNull(GraphQLInt())],
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
        $instance = newGraphQLScalarType([
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
        $instance = newGraphQLScalarType([
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
        $instance = newGraphQLObjectType([
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
    return newGraphQLSchema([
        'query'      => QueryRoot(),
        'types'      => [Cat(), Dog(), Human(), Alien()],
        'directives' => [
            GraphQLIncludeDirective(),
            GraphQLSkipDirective(),
            newGraphQLDirective([
                'name'      => 'onQuery',
                'locations' => ['QUERY'],
            ]),
            newGraphQLDirective([
                'name'      => 'onMutation',
                'locations' => ['MUTATION'],
            ]),
            newGraphQLDirective([
                'name'      => 'onSubscription',
                'locations' => ['SUBSCRIPTION'],
            ]),
            newGraphQLDirective([
                'name'      => 'onField',
                'locations' => ['FIELD'],
            ]),
            newGraphQLDirective([
                'name'      => 'onFragmentDefinition',
                'locations' => ['FRAGMENT_DEFINITION'],
            ]),
            newGraphQLDirective([
                'name'      => 'onFragmentSpread',
                'locations' => ['FRAGMENT_SPREAD'],
            ]),
            newGraphQLDirective([
                'name'      => 'onInlineFragment',
                'locations' => ['INLINE_FRAGMENT'],
            ]),
            newGraphQLDirective([
                'name'      => 'onSchema',
                'locations' => ['SCHEMA'],
            ]),
            newGraphQLDirective([
                'name'      => 'onScalar',
                'locations' => ['SCALAR'],
            ]),
            newGraphQLDirective([
                'name'      => 'onObject',
                'locations' => ['OBJECT'],
            ]),
            newGraphQLDirective([
                'name'      => 'onFieldDefinition',
                'locations' => ['FIELD_DEFINITION'],
            ]),
            newGraphQLDirective([
                'name'      => 'onArgumentDefinition',
                'locations' => ['ARGUMENT_DEFINITION'],
            ]),
            newGraphQLDirective([
                'name'      => 'onInterface',
                'locations' => ['INTERFACE'],
            ]),
            newGraphQLDirective([
                'name'      => 'onUnion',
                'locations' => ['UNION'],
            ]),
            newGraphQLDirective([
                'name'      => 'onEnum',
                'locations' => ['ENUM'],
            ]),
            newGraphQLDirective([
                'name'      => 'onEnumValue',
                'locations' => ['ENUM_VALUE'],
            ]),
            newGraphQLDirective([
                'name'      => 'onInputObject',
                'locations' => ['INPUT_OBJECT'],
            ]),
            newGraphQLDirective([
                'name'      => 'onInputFieldDefinition',
                'locations' => ['INPUT_FIELD_DEFINITION'],
            ]),
        ],
    ]);
}
