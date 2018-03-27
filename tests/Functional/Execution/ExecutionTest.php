<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Execution\ResolveInfo;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class ExecutionTest extends TestCase
{
    /**
     * throws if no document is provided
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testNoDocumentIsProvided()
    {
        $this->expectException(\TypeError::class);

        $schema = GraphQLSchema([
            'query' =>
                new ObjectType([
                    'name'   => 'Type',
                    'fields' => [
                        'a' => [
                            'type' => GraphQLString()
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
                            'type'    => GraphQLString(),
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

        $expected = new ExecutionResult(['a' => $rootValue], []);

        $this->assertEquals($expected, $result);
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
                            'type'    => GraphQLString(),
                            'resolve' => function () {
                                return 'world';
                            }
                        ]
                    ]
                ])
        ]);

        /** @var ExecutionResult $executionResult */
        $executionResult = graphql($schema, 'query Greeting {hello}');

        $expected = new ExecutionResult(['hello' => 'world'], []);

        $this->assertEquals($expected, $executionResult);
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
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Already Been Done';
                        }
                    ],
                    'b'      => [
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Boring';
                        }
                    ],
                    'c'      => [
                        'type'    => GraphQLList(GraphQLString()),
                        'resolve' => function () {
                            return ['Contrived', null, 'Confusing'];
                        }
                    ],
                    'deeper' => [
                        'type'    => GraphQLList($dataType),
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
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Apple';
                        }
                    ],
                    'b'       => [
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Banana';
                        }
                    ],
                    'c'       => [
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Cookie';
                        }
                    ],
                    'd'       => [
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Donut';
                        }
                    ],
                    'e'       => [
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Egg';
                        }
                    ],
                    'f'       => [
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Fish';
                        }
                    ],
                    'pic'     => [
                        'type'    => GraphQLString(),
                        'resolve' => function ($src, $args) {
                            return 'Pic of size: ' . ($args['size'] ?? 50);
                        },
                        'args'    => [
                            'size' => [
                                'type' => GraphQLInt(),
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
        $schema = GraphQLSchema([
            'query' =>
                GraphQLObjectType([
                    'name'   => 'Greeting',
                    'fields' => [
                        'greeting' => [
                            'type'    => GraphQLString(),
                            'resolve' => function ($source, $args, $context, $info) {
                                return sprintf('Hello %s', $args['name']);
                            },
                            'args'    => [
                                'name' => [
                                    'type' => GraphQLString(),
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

        $expected = new ExecutionResult(['greeting' => 'Hello Han Solo'], []);

        $this->assertEquals($expected, $executionResult);
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
                            'type'    => GraphQLInt(),
                            'resolve' => function () {
                                return 1000;
                            }
                        ],
                        'type'       => [
                            'type'    => GraphQLString(),
                            'resolve' => function () {
                                return 'Human';
                            }
                        ],
                        'friends'    => [
                            'type'    => GraphQLList(GraphQLString()),
                            'resolve' => function () {
                                return ['1002', '1003', '2000', '2001'];
                            }
                        ],
                        'appearsIn'  => [
                            'type'    => GraphQLList(GraphQLInt()),
                            'resolve' => function () {
                                return [4, 5, 6];
                            }
                        ],
                        'homePlanet' => [
                            'type'    => GraphQLString(),
                            'resolve' => function () {
                                return 'Tatooine';
                            }
                        ],
                    ]
                ])
        ]);

        /** @var ExecutionResult $executionResult */
        $executionResult = graphql($schema, 'query Human {id, type, friends, appearsIn, homePlanet}');

        $expected = new ExecutionResult([
            'id'         => 1000,
            'type'       => 'Human',
            'friends'    => ['1002', '1003', '2000', '2001'],
            'appearsIn'  => [4, 5, 6],
            'homePlanet' => 'Tatooine'
        ], []);

        $this->assertEquals($expected, $executionResult);
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
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Apple';
                        }
                    ],
                    'b'    => [
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Banana';
                        }
                    ],
                    'c'    => [
                        'type'    => GraphQLString(),
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

        $expected = new ExecutionResult([
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
        ], []);

        $this->assertEquals($expected, $executionResult);
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
                            'type'    => GraphQLString(),
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
        $this->assertEquals(GraphQLString(), $info->getReturnType());
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
                            'type'    => GraphQLString(),
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
                            'type'    => GraphQLInt(),
                            'args'    => [
                                'numArg'    => ['type' => GraphQLInt()],
                                'stringArg' => ['type' => GraphQLString()]
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
                        'sync'                => ['type' => GraphQLString()],
                        'syncError'           => ['type' => GraphQLString()],
                        'syncRawError'        => ['type' => GraphQLString()],
                        'syncReturnError'     => ['type' => GraphQLString()],
                        'syncReturnErrorList' => ['type' => GraphQLList(GraphQLString())],
                        'async'               => ['type' => GraphQLString()],
                        'asyncReject'         => ['type' => GraphQLString()],
                        'asyncRawReject'      => ['type' => GraphQLString()],
                        'asyncEmptyReject'    => ['type' => GraphQLString()],
                        'asyncError'          => ['type' => GraphQLString()],
                        'asyncRawError'       => ['type' => GraphQLString()],
                        'asyncReturnError'    => ['type' => GraphQLString()],
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
                        'type'    => GraphQLList(GraphQLObjectType([
                            'name'   => 'Food',
                            'fields' => [
                                'name' => [
                                    'type' => GraphQLString()
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
        $A = GraphQLObjectType([
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
                        'type'    => GraphQLNonNull($A),
                        'resolve' => function () {
                            return [];
                        }
                    ],
                    'throws'    => [
                        'type'    => GraphQLNonNull(GraphQLString()),
                        'resolve' => function () {
                            throw new \Exception('Catch me if you can!');
                        }
                    ]
                ];
            }
        ]);

        $queryType = GraphQLObjectType([
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

        $schema = GraphQLSchema([
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

        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => GraphQLString(),
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

        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => GraphQLString(),
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

        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => GraphQLString(),
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

        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => GraphQLString(),
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

        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => GraphQLString(),
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

        $schema = GraphQLSchema([
            'query'        => GraphQLObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => [
                        'type' => GraphQLString(),
                    ]
                ]
            ]),
            'mutation'     => GraphQLObjectType([
                'name'   => 'M',
                'fields' => [
                    'c' => [
                        'type' => GraphQLString(),
                    ]
                ]
            ]),
            'subscription' => GraphQLObjectType([
                'name'   => 'S',
                'fields' => [
                    'a' => [
                        'type' => GraphQLString(),
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

        $schema = GraphQLSchema([
            'query'    => GraphQLObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => [
                        'type' => GraphQLString(),
                    ]
                ]
            ]),
            'mutation' => GraphQLObjectType([
                'name'   => 'M',
                'fields' => [
                    'c' => [
                        'type' => GraphQLString(),
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

        $schema = GraphQLSchema([
            'query'        => GraphQLObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => [
                        'type' => GraphQLString(),
                    ]
                ]
            ]),
            'subscription' => GraphQLObjectType([
                'name'   => 'S',
                'fields' => [
                    'a' => [
                        'type' => GraphQLString(),
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

        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => ['type' => GraphQLString()],
                    'b' => ['type' => GraphQLString()],
                    'c' => ['type' => GraphQLString()],
                    'd' => ['type' => GraphQLString()],
                    'e' => ['type' => GraphQLString()],
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
}
