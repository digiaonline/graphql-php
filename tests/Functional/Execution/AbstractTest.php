<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\Boolean;
use function Digia\GraphQL\Type\newInterfaceType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\String;
use function Digia\GraphQL\Type\newUnionType;

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
        $PetInterfaceType = newInterfaceType([
            'name'   => 'Pet',
            'fields' => [
                'name' => ['type' => String()]
            ]
        ]);

        $DogType = newObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => String()],
                'woofs' => ['type' => Boolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        $CatType = newObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => String()],
                'meows' => ['type' => Boolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

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
        $DogType = newObjectType([
            'name'     => 'Dog',
            'fields'   => [
                'name'  => ['type' => String()],
                'woofs' => ['type' => Boolean()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        $CatType = newObjectType([
            'name'     => 'Cat',
            'fields'   => [
                'name'  => ['type' => String()],
                'meows' => ['type' => Boolean()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

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
        $PetInterfaceType = newInterfaceType([
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

        $DogType = newObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => String()],
                'woofs' => ['type' => Boolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        $CatType = newObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => String()],
                'meows' => ['type' => Boolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

        $HumanType = newObjectType([
            'name'     => 'Human',
            'fields'   => [
                'name' => ['type' => String()]
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Human;
            }
        ]);

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newList($PetInterfaceType),
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
        $DogType = newObjectType([
            'name'     => 'Dog',
            'fields'   => [
                'name'  => ['type' => String()],
                'woofs' => ['type' => Boolean()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        $CatType = newObjectType([
            'name'     => 'Cat',
            'fields'   => [
                'name'  => ['type' => String()],
                'meows' => ['type' => Boolean()],
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

        $HumanType = newObjectType([
            'name'     => 'Human',
            'fields'   => [
                'name' => ['type' => String()]
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Human;
            }
        ]);

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

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'pets' => [
                        'type'    => newList($PetUnionType),
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
        $PetInterfaceType = newInterfaceType([
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

        $DogType = newObjectType([
            'name'       => 'Dog',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => String()],
                'woofs' => ['type' => Boolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Dog;
            }
        ]);

        $CatType = newObjectType([
            'name'       => 'Cat',
            'interfaces' => [$PetInterfaceType],
            'fields'     => [
                'name'  => ['type' => String()],
                'meows' => ['type' => Boolean()],
            ],
            'isTypeOf'   => function ($obj) {
                return $obj instanceof Cat;
            }
        ]);

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
