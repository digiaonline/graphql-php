<?php

namespace Digia\GraphQL\Test\Functional\Execution;


use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\GraphQLUnionType;

class AbstractPromiseTest extends TestCase
{
    // EXECUTE: HANDLES EXECUTION OF ABSTRACT TYPES

    /**
     * isTypeOf used to resolve runtime type for Interface
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIsTypeOfUsedToResolveFunctionForInterface()
    {
        $PetInterfaceType = GraphQLInterfaceType([
            'name'   => 'Pet',
            'fields' => [
                'name' => ['type' => GraphQLString()]
            ]
        ]);

        $DogType = GraphQLObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => GraphQLString()],
                'woofs' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return \React\Promise\resolve(function ($obj) {
                    return $obj instanceof Dog;
                });
            }
        ]);

        $CatType = GraphQLObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => GraphQLString()],
                'meows' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return \React\Promise\resolve(function ($obj) {
                    return $obj instanceof Cat;
                });
            }
        ]);

        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => GraphQLList($PetInterfaceType),
                        'resolve' => function ($source, $args, $context, $info) {
                            return [
                                new Dog('Odie', true),
                                new Cat('Garfield', false)
                            ];
                        }
                    ]
                ],
            ]),
            'types' => [$DogType, $CatType],
        ]);

        $source = '{
          pets {
            name
            ... on Dog {
              woofs
            }
            ... on Cat {
              meows
            }
          }
        }';

        /** @var ExecutionResult $executionResult */
        $result = execute($schema, parse($source));

        $expected = new ExecutionResult([
            'pets' => [
                [
                    'name'  => 'Odie',
                    'woofs' => true,
                ],
                [
                    'name'  => 'Garfield',
                    'meows' => false,
                ]
            ]
        ], []);

        $this->assertEquals($expected, $result);
    }

    /**
     * resolveType on Union yields useful error
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testResolveTypeOnInterfaceYieldsUsefulError()
    {
        $DogType = GraphQLObjectType([
            'name'     => 'Dog',
            'fields'   => [
                'name'  => ['type' => GraphQLString()],
                'woofs' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        $CatType = GraphQLObjectType([
            'name'     => 'Cat',
            'fields'   => [
                'name'  => ['type' => GraphQLString()],
                'meows' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

        $HumanType = GraphQLObjectType([
            'name'     => 'Human',
            'fields'   => [
                'name' => ['type' => GraphQLString()]
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Human;
            }
        ]);

        $PetUnionType = GraphQLUnionType([
            'name'        => 'Pet',
            'types'       => [$DogType, $CatType],
            'resolveType' => function ($result, $context, $info) use ($DogType, $CatType, $HumanType) {
                if ($result instanceof Dog) {
                    return $DogType;
                }

                if ($result instanceof Cat) {
                    return $CatType;
                }

                if ($result instanceof Human) {
                    return $HumanType;
                }

                return null;
            }
        ]);

        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => GraphQLList($PetUnionType),
                        'resolve' => function ($source, $args, $context, $info) {
                            return \React\Promise\resolve([
                                new Dog('Odie', true),
                                new Cat('Garfield', false),
                                new Human('John')
                            ]);
                        }
                    ]
                ]
            ]),
        ]);

        $source = '{
          pets {
            name
            ... on Dog {
              woofs
            }
            ... on Cat {
              meows
            }
          }
        }';

        /** @var ExecutionResult $executionResult */
        $result = execute($schema, parse($source));

        $expected = new ExecutionResult([
            'pets' => [
                [
                    'name'  => 'Odie',
                    'woofs' => true,
                ],
                [
                    'name'  => 'Garfield',
                    'meows' => false,
                ],
                null
            ]
        ], [
            new ExecutionException('Runtime Object type "Human" is not a possible type for "Pet".'),
        ]);

        $this->assertEquals($expected, $result);
    }
}