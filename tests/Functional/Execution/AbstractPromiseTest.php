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
                return \React\Promise\resolve($obj instanceof Dog);
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
                return \React\Promise\resolve($obj instanceof Cat);
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
     * isTypeOf can be rejected
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIsTypeOfCanBeRejected()
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
                return \React\Promise\reject(new ExecutionException('We are testing this error'));
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
                return \React\Promise\resolve($obj instanceof Cat);
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

        $expected = [
            'data'   => [
                'pets' => [
                    null,
                    null
                ]
            ],
            'errors' => [
                [
                    'message'   => 'We are testing this error',
                    'locations' => null,
                    'path'      => ['pets', 0]
                ],
                [
                    'message'   => 'We are testing this error',
                    'locations' => null,
                    'path'      => ['pets', 1]
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * isTypeOf used to resolve runtime type for Union
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIsTypeOfUsedToResolveRuntimeTypeForUnion()
    {
        $DogType = GraphQLObjectType([
            'name'     => 'Dog',
            'fields'   => [
                'name'  => ['type' => GraphQLString()],
                'woofs' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf' => function ($obj) {
                return \React\Promise\resolve($obj instanceof Dog);
            }
        ]);

        $CatType = GraphQLObjectType([
            'name'     => 'Cat',
            'fields'   => [
                'name'  => ['type' => GraphQLString()],
                'meows' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf' => function ($obj) {
                return \React\Promise\resolve($obj instanceof Cat);
            }
        ]);

        $PetUnionType = GraphQLUnionType([
            'name'        => 'Pet',
            'types'       => [$DogType, $CatType],
            'resolveType' => function ($result, $context, $info) use ($DogType, $CatType) {
                if ($result instanceof Dog) {
                    return $DogType;
                }

                if ($result instanceof Cat) {
                    return $CatType;
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
                            return [
                                new Dog('Odie', true),
                                new Cat('Garfield', false)
                            ];
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
                ]
            ]
        ], []);

        $this->assertEquals($expected, $result);
    }

    /**
     * resolveType on Interface yields useful error
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testResolveTypeOnInterfaceYieldsUsefulError()
    {
        $PetInterfaceType = GraphQLInterfaceType([
            'name'        => 'Pet',
            'resolveType' => function ($obj) use (&$DogType, &$CatType, &$HumanType) {
                return \React\Promise\resolve(
                    $obj instanceof Dog
                        ? $DogType
                        : ($obj instanceof Cat
                        ? $CatType
                        : ($obj instanceof Human ? $HumanType : null)));
            }
        ]);

        $DogType = GraphQLObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => GraphQLString()],
                'woofs' => ['type' => GraphQLBoolean()],
            ]
        ]);

        $CatType = GraphQLObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => GraphQLString()],
                'meows' => ['type' => GraphQLBoolean()],
            ]
        ]);

        $HumanType = GraphQLObjectType([
            'name'   => 'Human',
            'fields' => [
                'name' => ['type' => GraphQLString()]
            ]
        ]);

        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => GraphQLList($PetInterfaceType),
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
            'types' => [$DogType, $CatType]
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

        $expected = [
            'data'   => [
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
            ],
            'errors' => [
                [
                    'message'   => 'Runtime Object type "Human" is not a possible type for "Pet".',
                    'locations' => null,
                    'path'      => ['pets', 2]
                ],
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * resolveType on Union yields useful error
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testResolveTypeOnUnionYieldsUsefulError()
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

        $expected = [
            'data'   => [
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
            ],
            'errors' => [
                [
                    'message'   => 'Runtime Object type "Human" is not a possible type for "Pet".',
                    'locations' => null,
                    'path'      => ['pets', 2]
                ],
            ]
        ];

        $this->assertArraySubset($expected, $result->toArray());
    }

    /**
     * resolveType allows resolving with type name
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testResolveTypeAllowsResolvingWithTypeName()
    {
        $PetInterfaceType = GraphQLInterfaceType([
            'name'        => 'Pet',
            'resolveType' => function ($obj) {
                return \React\Promise\resolve(
                    $obj instanceof Dog
                        ? 'Dog'
                        : ($obj instanceof Cat ? 'Cat' : null)
                );
            }
        ]);

        $DogType = GraphQLObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => GraphQLString()],
                'woofs' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Dog;
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
                return $obj instanceof Cat;
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
                ]
            ]),
            'types' => [$DogType, $CatType]
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

    //resolveType can be caught

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testResolveTypeCanBeCaught()
    {
        $PetInterfaceType = GraphQLInterfaceType([
            'name'        => 'Pet',
            'resolveType' => function ($obj) {
                return \React\Promise\reject(new ExecutionException('We are testing this error'));
            }
        ]);

        $DogType = GraphQLObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => GraphQLString()],
                'woofs' => ['type' => GraphQLBoolean()],
            ]
        ]);

        $CatType = GraphQLObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => GraphQLString()],
                'meows' => ['type' => GraphQLBoolean()],
            ]
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
                ]
            ]),
            'types' => [$DogType, $CatType]
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

        $expected = [
            'data'   => [
                'pets' => [
                    null,
                    null
                ]
            ],
            'errors' => [
                [
                    'message'   => 'We are testing this error',
                    'locations' => null,
                    'path'      => ['pets', 0]
                ],
                [
                    'message'   => 'We are testing this error',
                    'locations' => null,
                    'path'      => ['pets', 1]
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
