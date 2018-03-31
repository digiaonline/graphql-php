<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Execution\ResolveInfo;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Schema\Schema;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\Int;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\String;

class ExecutionTest extends TestCase
{
    /**
     * throws if no document is provided
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testNoDocumentIsProvided()
    {
        $this->expectException(\TypeError::class);

        $schema = newSchema([
            'query' =>
                new ObjectType([
                    'name'   => 'Type',
                    'fields' => [
                        'a' => [
                            'type' => String()
                        ]
                    ]
                ])
        ]);

        graphql($schema, null);
    }

    /**
     * throws if no schema is provided
     *
     * @throws \Exception
     */
    public function testNoSchemaIsProvided()
    {
        $this->expectException(\TypeError::class);

        graphql(null, '{field}');
    }

    /**
     * Test accepts an object with named properties as arguments
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testAcceptAnObjectWithNamedPropertiesAsArguments()
    {
        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Greeting',
                    'fields' => [
                        'a' => [
                            'type'    => String(),
                            'resolve' => function ($source, $args, $context, $info) {
                                return $source;
                            }
                        ]
                    ]
                ])
        ]);

        $rootValue = 'rootValue';
        $source    = 'query Example { a }';

        /** @var ExecutionResult $executionResult */
        $result = graphql($schema, $source, $rootValue);

        $this->assertEquals(['data' => ['a' => $rootValue]], $result);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testExecuteHelloQuery()
    {
        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Greeting',
                    'fields' => [
                        'hello' => [
                            'type'    => String(),
                            'resolve' => function () {
                                return 'world';
                            }
                        ]
                    ]
                ])
        ]);

        /** @var ExecutionResult $executionResult */
        $executionResult = graphql($schema, 'query Greeting {hello}');

        $this->assertEquals(['data' => ['hello' => 'world']], $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testExecuteArbitraryCode()
    {
        $deep = new ObjectType([
            'name'   => 'DeepDataType',
            'fields' => function () use (&$dataType) {
                return [
                    'a'      => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Already Been Done';
                        }
                    ],
                    'b'      => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Boring';
                        }
                    ],
                    'c'      => [
                        'type'    => newList(String()),
                        'resolve' => function () {
                            return ['Contrived', null, 'Confusing'];
                        }
                    ],
                    'deeper' => [
                        'type'    => newList($dataType),
                        'resolve' => function () {
                            return [
                                [
                                    'a' => 'Apple',
                                    'b' => 'Banana',
                                ],
                                null
                                ,
                                [
                                    'a' => 'Apple',
                                    'b' => 'Banana',
                                ]
                            ];
                        }
                    ]
                ];
            }
        ]);

        $dataType = new ObjectType([
            'name'   => 'DataType',
            'fields' => function () use (&$dataType, &$deep) {
                return [
                    'a'       => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Apple';
                        }
                    ],
                    'b'       => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Banana';
                        }
                    ],
                    'c'       => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Cookie';
                        }
                    ],
                    'd'       => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Donut';
                        }
                    ],
                    'e'       => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Egg';
                        }
                    ],
                    'f'       => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Fish';
                        }
                    ],
                    'pic'     => [
                        'type'    => String(),
                        'resolve' => function ($src, $args) {
                            return 'Pic of size: ' . ($args['size'] ?? 50);
                        },
                        'args'    => [
                            'size' => [
                                'type' => Int(),
                            ]
                        ],
                    ],
                    'promise' => [
                        'type'    => $dataType,
                        'resolve' => function () {
                            return [];
                        }
                    ],
                    'deep'    => [
                        'type'    => $deep,
                        'resolve' => function () {
                            return [];
                        }
                    ]
                ];
            }
        ]);

        $source = '
      query Example($size: Int) {
        a,
        b,
        x: c
        ...c
        f
        ...on DataType {
          pic(size: $size)
          promise {
            a
          }
        }
        deep {
          a
          b
          c
          deeper {
            a
            b
          }
        }
      }

      fragment c on DataType {
        d
        e
      }
    ';

        $schema = new Schema([
            'query' => $dataType
        ]);

        /** @var ExecutionResult $executionResult */
        $executionResult = execute($schema, parse($source), null, null, ['size' => 100]);

        $expected = new ExecutionResult([
            'a'       => 'Apple',
            'b'       => 'Banana',
            'x'       => 'Cookie',
            'd'       => 'Donut',
            'e'       => 'Egg',
            'f'       => 'Fish',
            'pic'     => 'Pic of size: 100',
            'promise' => [
                'a' => 'Apple'
            ],
            'deep'    => [
                'a'      => 'Already Been Done',
                'b'      => 'Boring',
                'c'      => ['Contrived', null, 'Confusing'],
                'deeper' => [
                    ['a' => 'Apple', 'b' => 'Banana'],
                    null,
                    ['a' => 'Apple', 'b' => 'Banana'],
                ]
            ]
        ], []);

        $this->assertEquals($expected, $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testExecuteQueryHelloWithArgs()
    {
        $schema = newSchema([
            'query' =>
                newObjectType([
                    'name'   => 'Greeting',
                    'fields' => [
                        'greeting' => [
                            'type'    => String(),
                            'resolve' => function ($source, $args, $context, $info) {
                                return sprintf('Hello %s', $args['name']);
                            },
                            'args'    => [
                                'name' => [
                                    'type' => String(),
                                ]
                            ]
                        ]
                    ]
                ])
        ]);

        $source         = 'query Hello($name: String) {greeting(name: $name)}';
        $variableValues = ['name' => 'Han Solo'];

        /** @var ExecutionResult $executionResult */
        $executionResult = graphql($schema, $source, '', null, $variableValues);

        $this->assertEquals(['data' => ['greeting' => 'Hello Han Solo']], $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testExecuteQueryWithMultipleFields()
    {
        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Human',
                    'fields' => [
                        'id'         => [
                            'type'    => Int(),
                            'resolve' => function () {
                                return 1000;
                            }
                        ],
                        'type'       => [
                            'type'    => String(),
                            'resolve' => function () {
                                return 'Human';
                            }
                        ],
                        'friends'    => [
                            'type'    => newList(String()),
                            'resolve' => function () {
                                return ['1002', '1003', '2000', '2001'];
                            }
                        ],
                        'appearsIn'  => [
                            'type'    => newList(Int()),
                            'resolve' => function () {
                                return [4, 5, 6];
                            }
                        ],
                        'homePlanet' => [
                            'type'    => String(),
                            'resolve' => function () {
                                return 'Tatooine';
                            }
                        ],
                    ]
                ])
        ]);

        /** @var ExecutionResult $executionResult */
        $executionResult = graphql($schema, 'query Human {id, type, friends, appearsIn, homePlanet}');

        $this->assertEquals([
            'data' => [
                'id'         => 1000,
                'type'       => 'Human',
                'friends'    => ['1002', '1003', '2000', '2001'],
                'appearsIn'  => [4, 5, 6],
                'homePlanet' => 'Tatooine'
            ]
        ], $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testMergeParallelFragments()
    {
        $source = <<<SRC
{ a, ...FragOne, ...FragTwo }
fragment FragOne on Type {
    b
    deep { b, deeper: deep { b } }
}
fragment FragTwo on Type {
    c
    deep { c, deeper: deep { c } }
}
SRC;

        $Type = new ObjectType([
            'name'   => 'Type',
            'fields' => function () use (&$Type) {
                return [
                    'a'    => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Apple';
                        }
                    ],
                    'b'    => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Banana';
                        }
                    ],
                    'c'    => [
                        'type'    => String(),
                        'resolve' => function () {
                            return 'Cherry';
                        }
                    ],
                    'deep' => [
                        'type'    => $Type,
                        'resolve' => function () {
                            return [];
                        }
                    ]
                ];
            }
        ]);

        $schema = new Schema([
            'query' => $Type
        ]);

        /** @var ExecutionResult $executionResult */
        $executionResult = graphql($schema, $source);

        $this->assertEquals([
            'data' => [
                'a'    => 'Apple',
                'b'    => 'Banana',
                'c'    => 'Cherry',
                'deep' => [
                    'b'      => 'Banana',
                    'c'      => 'Cherry',
                    'deeper' => [
                        'b' => 'Banana',
                        'c' => 'Cherry'
                    ]
                ]
            ]
        ], $executionResult);
    }

    /**
     * provides info about current execution state
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testProvidesInfoAboutCurrentExecutionState()
    {
        $info   = null;
        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Test',
                    'fields' => [
                        'test' => [
                            'type'    => String(),
                            'resolve' => function ($source, $args, $context, $_info) use (&$info) {
                                $info = $_info;
                            }
                        ]
                    ]
                ])
        ]);

        $rootValue = [
            'rootValue' => 'val'
        ];

        $ast = parse('query ($var: String) { result: test }');
        execute($schema, $ast, $rootValue, null, ['var' => 123]);

        /** @var ResolveInfo $info */
        $this->assertEquals('test', $info->getFieldName());
        $this->assertEquals(1, count($info->getFieldNodes()));
        $this->assertEquals(
            $ast->getDefinitions()[0]->getSelectionSet()->getSelections()[0],
            $info->getFieldNodes()[0]
        );
        $this->assertEquals(String(), $info->getReturnType());
        $this->assertEquals($schema->getQueryType(), $info->getParentType());
        $this->assertEquals(["result"], $info->getPath()); // { prev: undefined, key: 'result' }
        $this->assertEquals($schema, $info->getSchema());
        $this->assertEquals($rootValue, $info->getRootValue());
        $this->assertEquals($ast->getDefinitions()[0], $info->getOperation());
        $this->assertEquals(['var' => 123], $info->getVariableValues());
    }

    /**
     * Threads root value context correctly
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testThreadsRootValueContextCorrectly()
    {
        $resolvedRootValue = null;
        $schema            = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Test',
                    'fields' => [
                        'a' => [
                            'type'    => String(),
                            'resolve' => function ($rootValue) use (&$resolvedRootValue) {
                                $resolvedRootValue = $rootValue;
                            }
                        ]
                    ]
                ])
        ]);

        $data = ['contextThing' => 'thing'];

        $ast = parse('query Example { a }');

        execute($schema, $ast, $data);

        $this->assertEquals('thing', $resolvedRootValue['contextThing']);
    }

    /**
     * Correctly threads arguments
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testCorrectlyThreadsArguments()
    {
        $resolvedArgs = null;

        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Type',
                    'fields' => [
                        'b' => [
                            'type'    => Int(),
                            'args'    => [
                                'numArg'    => ['type' => Int()],
                                'stringArg' => ['type' => String()]
                            ],
                            'resolve' => function ($source, $args) use (&$resolvedArgs) {
                                $resolvedArgs = $args;
                            }
                        ]
                    ]
                ])
        ]);

        execute($schema, parse('query Example { b(numArg: 123, stringArg: "foo") }'));

        $this->assertEquals(['numArg' => 123, 'stringArg' => 'foo'], $resolvedArgs);
    }

    /**
     * Nulls out error subtrees
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testNullsOutErrorsSubTrees()
    {
        $source = '{
          sync
          syncError
          syncRawError
          syncReturnError
          syncReturnErrorList
          async
          asyncReject
          asyncRawReject
          asyncEmptyReject
          asyncError
          asyncRawError
          asyncReturnError
        }';

        $data = [
            'sync'                => function () {
                return 'sync';
            },
            'syncError'           => function () {
                throw new \Exception('Error getting syncError');
            },
            'syncRawError'        => function () {
                throw new \Exception('Error getting syncRawError');
            },
            'syncReturnError'     => function () {
                return new \Exception('Error getting syncReturnError');
            },
            'syncReturnErrorList' => function () {
                return [
                    'sync0',
                    new \Exception('Error getting syncReturnErrorList1'),
                    'sync2',
                    new \Exception('Error getting syncReturnErrorList3'),
                ];
            },
            'async'               => function () {
                return \React\Promise\resolve('async');
            },
            'asyncReject'         => function () {
                return new \React\Promise\Promise(function ($resolve, $reject) {
                    $reject(new \Exception('Error getting asyncReject'));
                });
            },
            'asyncRawReject'      => function () {
                return \React\Promise\reject('Error getting asyncRawReject');
            },
            'asyncEmptyReject'    => function () {
                return \React\Promise\reject(null);
            },
            'asyncError'          => function () {
                return new \React\Promise\Promise(function ($resolve, $reject) {
                    throw new \Exception('Error getting asyncError');
                });
            },
            'asyncRawError'       => function () {
                return new \React\Promise\Promise(function () {
                    throw new \Exception('Error getting asyncRawError');
                });
            },
            'asyncReturnError'    => function () {
                return \React\Promise\resolve(new \Exception('Error getting asyncReturnError'));
            },
        ];

        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Type',
                    'fields' => [
                        'sync'                => ['type' => String()],
                        'syncError'           => ['type' => String()],
                        'syncRawError'        => ['type' => String()],
                        'syncReturnError'     => ['type' => String()],
                        'syncReturnErrorList' => ['type' => newList(String())],
                        'async'               => ['type' => String()],
                        'asyncReject'         => ['type' => String()],
                        'asyncRawReject'      => ['type' => String()],
                        'asyncEmptyReject'    => ['type' => String()],
                        'asyncError'          => ['type' => String()],
                        'asyncRawError'       => ['type' => String()],
                        'asyncReturnError'    => ['type' => String()],
                    ]
                ])
        ]);

        $result = execute($schema, parse($source), $data);

        $this->assertEquals([
            'data'   => [
                'sync'                => 'sync',
                'syncError'           => null,
                'syncRawError'        => null,
                'syncReturnError'     => null,
                'syncReturnErrorList' => ['sync0', null, 'sync2', null],
                'async'               => 'async',
                'asyncReject'         => null,
                'asyncRawReject'      => null,
                'asyncEmptyReject'    => null,
                'asyncError'          => null,
                'asyncRawError'       => null,
                'asyncReturnError'    => null,
            ],
            'errors' => [
                [
                    'message'   => 'Error getting syncError',
                    'locations' => [
                        [
                            'line'   => 3,
                            'column' => 11
                        ]
                    ],
                    'path'      => ['syncError']
                ],
                [
                    'message'   => 'Error getting syncRawError',
                    'locations' => [
                        [
                            'line'   => 4,
                            'column' => 11
                        ]
                    ],
                    'path'      => ['syncRawError']
                ],
                [
                    'message'   => 'Error getting syncReturnError',
                    'locations' => [
                        [
                            'line'   => 5,
                            'column' => 11
                        ]
                    ],
                    'path'      => ['syncReturnError']
                ],
                [
                    'message'   => 'Error getting syncReturnErrorList1',
                    'locations' => [
                        [
                            'line'   => 6,
                            'column' => 11
                        ]
                    ],
                    'path'      => ['syncReturnErrorList', 1]
                ],
                [
                    'message'   => 'Error getting syncReturnErrorList3',
                    'locations' => [
                        [
                            'line'   => 6,
                            'column' => 11
                        ]
                    ],
                    'path'      => ['syncReturnErrorList', 3]
                ],
                [
                    'message'   => 'Error getting asyncReject',
                    'locations' => [
                        [
                            'line'   => 8,
                            'column' => 11
                        ]
                    ],
                    'path'      => ['asyncReject']
                ],
                [
                    'message'   => 'Error getting asyncRawReject',
                    'locations' => null, //@TODO This should not be null
                    'path'      => ['asyncRawReject']
                ],
                [
                    'message'   => 'An unknown error occurred.',
                    'locations' => null, //@TODO This should not be null
                    'path'      => ['asyncEmptyReject']
                ],
                [
                    'message'   => 'Error getting asyncError',
                    'locations' => [
                        [
                            'line'   => 11,
                            'column' => 11
                        ]
                    ],
                    'path'      => ['asyncError']
                ],
                [
                    'message'   => 'Error getting asyncRawError',
                    'locations' => [
                        [
                            'line'   => 12,
                            'column' => 11
                        ]
                    ],
                    'path'      => ['asyncRawError']
                ],
                [
                    'message'   => 'Error getting asyncReturnError',
                    'locations' => [
                        [
                            'line'   => 13,
                            'column' => 11
                        ]
                    ],
                    'path'      => ['asyncReturnError']
                ],
            ]
        ], $result->toArray());
    }

    //nulls error subtree for promise rejection

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testNullsErrorSubTreeForPromiseRejection()
    {
        $query = '
          query {
            foods {
              name
            }
          }
        ';

        $schema = new Schema([
            'query' => new ObjectType([
                'name'   => 'Type',
                'fields' => [
                    'foods' => [
                        'type'    => newList(newObjectType([
                            'name'   => 'Food',
                            'fields' => [
                                'name' => [
                                    'type' => String()
                                ]
                            ]
                        ])),
                        'resolve' => function () {
                            return \React\Promise\reject(new \Exception('Dangit'));
                        }
                    ],
                ]
            ])
        ]);

        $result = execute($schema, parse($query));

        $this->assertEquals([
            'data'   => [
                'foods' => null
            ],
            'errors' => [
                [
                    'message'   => 'Dangit',
                    'locations' => [
                        [
                            'line'   => 3,
                            'column' => 13
                        ]
                    ],
                    'path'      => ['foods']
                ]
            ]
        ], $result->toArray());
    }

    //Full response path is included for non-nullable fields

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testFullResponsePathIsIncludedForNonNullableFields()
    {
        $A = newObjectType([
            'name'   => 'A',
            'fields' => function () use (&$A) {
                return [
                    'nullableA' => [
                        'type'    => $A,
                        'resolve' => function () {
                            return [];
                        }
                    ],
                    'nonNullA'  => [
                        'type'    => newNonNull($A),
                        'resolve' => function () {
                            return [];
                        }
                    ],
                    'throws'    => [
                        'type'    => newNonNull(String()),
                        'resolve' => function () {
                            throw new \Exception('Catch me if you can!');
                        }
                    ]
                ];
            }
        ]);

        $queryType = newObjectType([
            'name'   => 'query',
            'fields' => function () use (&$A) {
                return [
                    'nullableA' => [
                        'type'    => $A,
                        'resolve' => function () {
                            return [];
                        }
                    ]
                ];
            }
        ]);

        $schema = newSchema([
            'query' => $queryType
        ]);

        $query = '
          query {
            nullableA {
              aliasedA: nullableA {
                nonNullA {
                  anotherA: nonNullA {
                    throws
                  }
                }
              }
            }
          }';

        $result = execute($schema, parse($query));

        $this->assertEquals([
            'data'   => [
                'nullableA' => [
                    'aliasedA' => null
                ]
            ],
            'errors' => [
                [
                    'message'   => 'Catch me if you can!',
                    'locations' => [
                        [
                            'line'   => 7,
                            'column' => 21
                        ]
                    ],
                    'path'      => [
                        'nullableA',
                        'aliasedA',
                        'nonNullA',
                        'anotherA',
                        'throws'
                    ]
                ]
            ]
        ], $result->toArray());
    }

    /**
     * Uses the inline operation if no operation name is provided
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUsesTheInlineOperationIfNoOperationIsProvided()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $result = execute($schema, parse('{ a }'), $rootValue);

        $this->assertEquals([
            'data' => [
                'a' => 'b',
            ]
        ], $result->toArray());
    }

    /**
     * Uses the named operation if operation name is provided
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUsesTheNamedOperationIfOperationNameIsProvided()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $query  = 'query Example { first: a } query OtherExample { second: a }';
        $result = execute($schema, parse($query), $rootValue, null, [], 'OtherExample');

        $this->assertEquals([
            'data' => [
                'second' => 'b',
            ]
        ], $result->toArray());
    }

    /**
     * Provides error if no operation is provided
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testProvidesErrorIfNoOperationIsProvided()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $query  = 'fragment Example on Type { a }';
        $result = execute($schema, parse($query), $rootValue);

        $this->assertEquals([
            'data'   => null,
            'errors' => [
                [
                    'message'   => 'Must provide an operation.',
                    'locations' => null,
                    'path'      => null
                ]
            ]
        ], $result->toArray());
    }


    /**
     * Errors if no op name is provided with multiple operations
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testErrorsIfNoOpNameIsProvidedWithMultipleOperations()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $query  = 'query Example { a } query OtherExample { a }';
        $result = execute($schema, parse($query), $rootValue);

        $this->assertEquals([
            'data'   => null,
            'errors' => [
                [
                    'message'   => 'Must provide operation name if query contains multiple operations.',
                    'locations' => null,
                    'path'      => null
                ]
            ]
        ], $result->toArray());
    }

    /**
     * Errors if unknown operation name is provided
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testErrorsIfUnknownOperationNameIsProvided()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $query  = 'query Example { a } query OtherExample { a }';
        $result = execute($schema, parse($query), $rootValue, [], [], 'UnknownExample');

        $this->assertEquals([
            'data'   => null,
            'errors' => [
                [
                    'message'   => 'Unknown operation named "UnknownExample".',
                    'locations' => null,
                    'path'      => null
                ]
            ]
        ], $result->toArray());
    }


    /**
     * Uses the query schema for queries
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUsesTheQuerySchemaForQueries()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query'        => newObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ]),
            'mutation'     => newObjectType([
                'name'   => 'M',
                'fields' => [
                    'c' => [
                        'type' => String(),
                    ]
                ]
            ]),
            'subscription' => newObjectType([
                'name'   => 'S',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $query  = 'query Q { a } mutation M { c } subscription S { a }';
        $result = execute($schema, parse($query), $rootValue, [], [], 'Q');

        $this->assertEquals([
            'data' => [
                'a' => 'b',
            ]
        ], $result->toArray());
    }

    /**
     * Uses the mutation schema for mutations
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUsesTheMutationSchemaForMutations()
    {
        $rootValue = ['a' => 'b', 'c' => 'd'];

        $schema = newSchema([
            'query'    => newObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ]),
            'mutation' => newObjectType([
                'name'   => 'M',
                'fields' => [
                    'c' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $query  = 'query Q { a } mutation M { c } subscription S { a }';
        $result = execute($schema, parse($query), $rootValue, [], [], 'M');

        $this->assertEquals([
            'data' => [
                'c' => 'd',
            ]
        ], $result->toArray());
    }

    /**
     * Uses the subscription schema for subscriptions
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUsesTheSubscriptionSchemaForSubscriptions()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query'        => newObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ]),
            'subscription' => newObjectType([
                'name'   => 'S',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $query  = 'query Q { a } subscription S { a }';
        $result = execute($schema, parse($query), $rootValue, [], [], 'S');

        $this->assertEquals([
            'data' => [
                'a' => 'b',
            ]
        ], $result->toArray());
    }

    /**
     * Correct field ordering despite execution order
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testCorrectFieldOrderingDespiteExecutionOrder()
    {
        $rootValue = [
            'a' => 'a',
            'b' => function () {
                return \React\Promise\resolve('b');
            },
            'c' => 'c',
            'd' => function () {
                return \React\Promise\resolve('d');
            },
            'e' => 'e'
        ];

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => ['type' => String()],
                    'b' => ['type' => String()],
                    'c' => ['type' => String()],
                    'd' => ['type' => String()],
                    'e' => ['type' => String()],
                ]
            ])
        ]);

        $query  = '{ a, b, c, d, e }';
        $result = execute($schema, parse($query), $rootValue);

        $this->assertEquals([
            'data' => [
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
                'd' => 'd',
                'e' => 'e',
            ]
        ], $result->toArray());
    }

    /**
     * Avoid recursions
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAvoidRecursions()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $query  = 'query Q {
            a
            ...Frag
            ...Frag
          }
    
          fragment Frag on Type {
            a,
            ...Frag
          }';
        $result = execute($schema, parse($query), $rootValue, [], [], 'Q');

        $this->assertEquals([
            'data' => [
                'a' => 'b',
            ]
        ], $result->toArray());
    }

    /**
     * Does not include illegal fields in output
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testDoesNotIncludeIllegalFieldsInOutput()
    {
        $schema = newSchema([
            'query'    => new ObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => ['type' => String()],
                ]
            ]),
            'mutation' => new ObjectType([
                'name'   => 'M',
                'fields' => [
                    'c' => ['type' => String()],
                ]
            ])
        ]);


        $executionResult = execute($schema, parse('mutation M { thisIsIllegalDontIncludeMe }'));

        $expected = new ExecutionResult([], []);

        $this->assertEquals($expected, $executionResult);
    }


    /**
     * Fails when an isTypeOf check is not met
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testFailsWhenAnIsTypeOfCheckIsNotMet()
    {
        $SpecialType = newObjectType([
            'name'     => 'SpecialType',
            'fields'   => [
                'value' => [
                    'type' => String()
                ]
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Special;
            },
        ]);

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => function () use (&$SpecialType) {
                    return [
                        'specials' => [
                            'type'    => newList($SpecialType),
                            'resolve' => function ($root) {
                                return $root['specials'];
                            }
                        ]
                    ];
                }
            ])
        ]);

        $rootValue = [
            'specials' => [
                new Special('foo'),
                new NotSpecial('bar')
            ]
        ];

        $query  = '{ specials { value } }';
        $result = execute($schema, parse($query), $rootValue);

        $this->assertEquals(1, count($result->getErrors()));
        $this->assertEquals([
            'data'   => [
                'specials' => [
                    ['value' => 'foo'],
                    null
                ]
            ],
            'errors' => [
                [
                    'message'   => 'Expected value of type "SpecialType" but received: Object.',
                    'locations' => null,
                    'path'      => ['specials', 1]
                ],
            ]
        ], $result->toArray());
    }

    /**
     * Executes ignoring invalid non-executable definitions
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testExecutesIgnoringInvalidNonExecutableDefinitions()
    {
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'foo' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $query = '{ foo } type Query { bar: String }';

        $result = execute($schema, parse($query));

        $this->assertEquals([
            'data' => [
                'foo' => null,
            ]
        ], $result->toArray());
    }

    /**
     * Uses a custom field resolver
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUsesACustomFieldResolver()
    {
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'foo' => [
                        'type' => String(),
                    ]
                ]
            ])
        ]);

        $query = '{ foo }';

        $customResolver = function ($source, $args, $context, ResolveInfo $info) {
            return $info->getFieldName();
        };

        $result = execute($schema, parse($query), [], [], [], null, $customResolver);

        $this->assertEquals([
            'data' => [
                'foo' => 'foo',
            ]
        ], $result->toArray());
    }
}

class Special
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}

class NotSpecial
{
    public $value;

    public function __construct($value)
    {
        $this->value = $value;
    }
}
