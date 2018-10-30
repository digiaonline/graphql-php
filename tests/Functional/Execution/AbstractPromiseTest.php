<?php

namespace Digia\GraphQL\Test\Functional\Execution;


use Digia\GraphQL\Execution\ExecutionException;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\booleanType;
use function Digia\GraphQL\Type\newInterfaceType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;
use function Digia\GraphQL\Type\newUnionType;

class AbstractPromiseTest extends TestCase
{
    // EXECUTE: HANDLES EXECUTION OF ABSTRACT TYPES

    /**
     * isTypeOf used to resolve runtime type for Interface
     */
    public function testIsTypeOfUsedToResolveFunctionForInterface()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $PetInterfaceType = newInterfaceType([
            'name'   => 'Pet',
            'fields' => [
                'name' => ['type' => stringType()]
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $DogType = newObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'woofs' => ['type' => booleanType()],
            ],
            'isTypeOf'   => function ($obj) {
                return \React\Promise\resolve($obj instanceof Dog);
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $CatType = newObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'meows' => ['type' => booleanType()],
            ],
            'isTypeOf'   => function ($obj) {
                return \React\Promise\resolve($obj instanceof Cat);
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newList($PetInterfaceType),
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

        /** @noinspection PhpUnhandledExceptionInspection */
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
     */
    public function testIsTypeOfCanBeRejected()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $PetInterfaceType = newInterfaceType([
            'name'   => 'Pet',
            'fields' => [
                'name' => ['type' => stringType()]
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $DogType = newObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'woofs' => ['type' => booleanType()],
            ],
            'isTypeOf'   => function ($obj) {
                return \React\Promise\reject(new ExecutionException('We are testing this error'));
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $CatType = newObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'meows' => ['type' => booleanType()],
            ],
            'isTypeOf'   => function ($obj) {
                return \React\Promise\resolve($obj instanceof Cat);
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newList($PetInterfaceType),
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

        /** @noinspection PhpUnhandledExceptionInspection */
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
     */
    public function testIsTypeOfUsedToResolveRuntimeTypeForUnion()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $DogType = newObjectType([
            'name'     => 'Dog',
            'fields'   => [
                'name'  => ['type' => stringType()],
                'woofs' => ['type' => booleanType()],
            ],
            'isTypeOf' => function ($obj) {
                return \React\Promise\resolve($obj instanceof Dog);
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $CatType = newObjectType([
            'name'     => 'Cat',
            'fields'   => [
                'name'  => ['type' => stringType()],
                'meows' => ['type' => booleanType()],
            ],
            'isTypeOf' => function ($obj) {
                return \React\Promise\resolve($obj instanceof Cat);
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $PetUnionType = newUnionType([
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newList($PetUnionType),
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

        /** @noinspection PhpUnhandledExceptionInspection */
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
     */
    public function testResolveTypeOnInterfaceYieldsUsefulError()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $PetInterfaceType = newInterfaceType([
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $DogType = newObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'woofs' => ['type' => booleanType()],
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $CatType = newObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'meows' => ['type' => booleanType()],
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $HumanType = newObjectType([
            'name'   => 'Human',
            'fields' => [
                'name' => ['type' => stringType()]
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newList($PetInterfaceType),
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

        /** @noinspection PhpUnhandledExceptionInspection */
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
     */
    public function testResolveTypeOnUnionYieldsUsefulError()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $DogType = newObjectType([
            'name'     => 'Dog',
            'fields'   => [
                'name'  => ['type' => stringType()],
                'woofs' => ['type' => booleanType()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $CatType = newObjectType([
            'name'     => 'Cat',
            'fields'   => [
                'name'  => ['type' => stringType()],
                'meows' => ['type' => booleanType()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $HumanType = newObjectType([
            'name'     => 'Human',
            'fields'   => [
                'name' => ['type' => stringType()]
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Human;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $PetUnionType = newUnionType([
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newList($PetUnionType),
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

        /** @noinspection PhpUnhandledExceptionInspection */
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
     */
    public function testResolveTypeAllowsResolvingWithTypeName()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $PetInterfaceType = newInterfaceType([
            'name'        => 'Pet',
            'resolveType' => function ($obj) {
                return \React\Promise\resolve(
                    $obj instanceof Dog
                        ? 'Dog'
                        : ($obj instanceof Cat ? 'Cat' : null)
                );
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $DogType = newObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'woofs' => ['type' => booleanType()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $CatType = newObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'meows' => ['type' => booleanType()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newList($PetInterfaceType),
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

        /** @noinspection PhpUnhandledExceptionInspection */
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
     * resolveType can be caught
     */
    public function testResolveTypeCanBeCaught()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $PetInterfaceType = newInterfaceType([
            'name'        => 'Pet',
            'resolveType' => function ($obj) {
                return \React\Promise\reject(new ExecutionException('We are testing this error'));
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $DogType = newObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'woofs' => ['type' => booleanType()],
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $CatType = newObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => stringType()],
                'meows' => ['type' => booleanType()],
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newList($PetInterfaceType),
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

        /** @noinspection PhpUnhandledExceptionInspection */
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
