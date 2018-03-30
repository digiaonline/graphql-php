<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\newGraphQLInterfaceType;
use function Digia\GraphQL\Type\newGraphQLList;
use function Digia\GraphQL\Type\newGraphQLObjectType;
use function Digia\GraphQL\Type\newGraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\newGraphQLUnionType;

class AbstractTest extends TestCase
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
        $PetInterfaceType = newGraphQLInterfaceType([
            'name'   => 'Pet',
            'fields' => [
                'name' => ['type' => GraphQLString()]
            ]
        ]);

        $DogType = newGraphQLObjectType([
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

        $CatType = newGraphQLObjectType([
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

        $schema = newGraphQLSchema([
            'query' => newGraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newGraphQLList($PetInterfaceType),
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
     * isTypeOf used to resolve runtime type for Union
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testIsTypeOfUsedToResolveRuntimeTypeForUnion()
    {
        $DogType = newGraphQLObjectType([
            'name'     => 'Dog',
            'fields'   => [
                'name'  => ['type' => GraphQLString()],
                'woofs' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        $CatType = newGraphQLObjectType([
            'name'     => 'Cat',
            'fields'   => [
                'name'  => ['type' => GraphQLString()],
                'meows' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

        $PetUnionType = newGraphQLUnionType([
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

        $schema = newGraphQLSchema([
            'query' => newGraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newGraphQLList($PetUnionType),
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

        $this->assertEquals($expected->getData(), $result->getData());
    }

    /**
     * resolveType on Interface yields useful error
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testResolveTypeOnInterfaceYieldsUsefulError()
    {
        $PetInterfaceType = newGraphQLInterfaceType([
            'name'        => 'Pet',
            'resolveType' => function ($result, $context, $info) use (&$DogType, &$CatType, &$HumanType) {
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

        $DogType = newGraphQLObjectType([
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

        $CatType = newGraphQLObjectType([
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

        $HumanType = newGraphQLObjectType([
            'name'     => 'Human',
            'fields'   => [
                'name' => ['type' => GraphQLString()]
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Human;
            }
        ]);

        $schema = newGraphQLSchema([
            'query' => newGraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newGraphQLList($PetInterfaceType),
                        'resolve' => function ($source, $args, $context, $info) {
                            return [
                                new Dog('Odie', true),
                                new Cat('Garfield', false),
                                new Human('John')
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
                ],
                null
            ]
        ], [
            new ExecutionException(
                'Runtime Object type "Human" is not a possible type for "Pet".',
                null,
                null,
                null,
                ['pets', 2]
            ),
        ]);

        $this->assertArraySubset($expected->toArray(), $result->toArray());
    }

    /**
     * resolveType on Union yields useful error
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testResolveTypeOnUnionYieldsUseFulError()
    {
        $DogType = newGraphQLObjectType([
            'name'     => 'Dog',
            'fields'   => [
                'name'  => ['type' => GraphQLString()],
                'woofs' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        $CatType = newGraphQLObjectType([
            'name'     => 'Cat',
            'fields'   => [
                'name'  => ['type' => GraphQLString()],
                'meows' => ['type' => GraphQLBoolean()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

        $HumanType = newGraphQLObjectType([
            'name'     => 'Human',
            'fields'   => [
                'name' => ['type' => GraphQLString()]
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Human;
            }
        ]);

        $PetUnionType = newGraphQLUnionType([
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

        $schema = newGraphQLSchema([
            'query' => newGraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newGraphQLList($PetUnionType),
                        'resolve' => function ($source, $args, $context, $info) {
                            return [
                                new Dog('Odie', true),
                                new Cat('Garfield', false),
                                new Human('John')
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
                ],
                null
            ]
        ], [
            new ExecutionException(
                'Runtime Object type "Human" is not a possible type for "Pet".',
                null,
                null,
                null,
                ['pets', 2]
            ),
        ]);

        $this->assertArraySubset($expected->toArray(), $result->toArray());
    }

    /**
     * resolveType allows resolving with type name
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testResolveTypeAllowsResolvingWithTypeName()
    {
        $PetInterfaceType = newGraphQLInterfaceType([
            'name'        => 'Pet',
            'resolveType' => function ($result, $context, $info) {
                if ($result instanceof Dog) {
                    return 'Dog';
                }

                if ($result instanceof Cat) {
                    return 'Cat';
                }

                return null;
            }
        ]);

        $DogType = newGraphQLObjectType([
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

        $CatType = newGraphQLObjectType([
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

        $schema = newGraphQLSchema([
            'query' => newGraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newGraphQLList($PetInterfaceType),
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
}
