<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Execution\ResolveInfo;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\intType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;

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
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => stringType()
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
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Greeting',
                'fields' => [
                    'a' => [
                        'type'    => stringType(),
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

        $this->assertSame(['data' => ['a' => $rootValue]], $result);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testExecuteHelloQuery()
    {
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Greeting',
                'fields' => [
                    'hello' => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'world';
                        }
                    ]
                ]
            ])
        ]);

        /** @var ExecutionResult $executionResult */
        $executionResult = graphql($schema, 'query Greeting {hello}');

        $this->assertSame(['data' => ['hello' => 'world']], $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     */
    public function testExecuteArbitraryCode()
    {
        $deep = newObjectType([
            'name'   => 'DeepDataType',
            'fields' => function () use (&$dataType) {
                return [
                    'a'      => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Already Been Done';
                        }
                    ],
                    'b'      => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Boring';
                        }
                    ],
                    'c'      => [
                        'type'    => newList(stringType()),
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

        $dataType = newObjectType([
            'name'   => 'DataType',
            'fields' => function () use (&$dataType, &$deep) {
                return [
                    'a'       => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Apple';
                        }
                    ],
                    'b'       => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Banana';
                        }
                    ],
                    'c'       => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Cookie';
                        }
                    ],
                    'd'       => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Donut';
                        }
                    ],
                    'e'       => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Egg';
                        }
                    ],
                    'f'       => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Fish';
                        }
                    ],
                    'pic'     => [
                        'type'    => stringType(),
                        'resolve' => function ($src, $args) {
                            return 'Pic of size: ' . ($args['size'] ?? 50);
                        },
                        'args'    => [
                            'size' => [
                                'type' => intType(),
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

        $schema = newSchema(['query' => $dataType]);

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
            'query' => newObjectType([
                'name'   => 'Greeting',
                'fields' => [
                    'greeting' => [
                        'type'    => stringType(),
                        'resolve' => function ($source, $args, $context, $info) {
                            return sprintf('Hello %s', $args['name']);
                        },
                        'args'    => [
                            'name' => [
                                'type' => stringType(),
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

        $this->assertSame(['data' => ['greeting' => 'Hello Han Solo']], $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testExecuteQueryWithMultipleFields()
    {
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Human',
                'fields' => [
                    'id'         => [
                        'type'    => intType(),
                        'resolve' => function () {
                            return 1000;
                        }
                    ],
                    'type'       => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Human';
                        }
                    ],
                    'friends'    => [
                        'type'    => newList(stringType()),
                        'resolve' => function () {
                            return ['1002', '1003', '2000', '2001'];
                        }
                    ],
                    'appearsIn'  => [
                        'type'    => newList(intType()),
                        'resolve' => function () {
                            return [4, 5, 6];
                        }
                    ],
                    'homePlanet' => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Tatooine';
                        }
                    ],
                ],
            ]),
        ]);

        /** @var ExecutionResult $executionResult */
        $executionResult = graphql($schema, 'query Human {id, type, friends, appearsIn, homePlanet}');

        $this->assertSame([
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
        $source = '
        { a, ...FragOne, ...FragTwo }
        fragment FragOne on Type {
            b
            deep { b, deeper: deep { b } }
        }
        fragment FragTwo on Type {
            c
            deep { c, deeper: deep { c } }
        }
        ';

        $type = newObjectType([
            'name'   => 'Type',
            'fields' => function () use (&$type) {
                return [
                    'a'    => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Apple';
                        }
                    ],
                    'b'    => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Banana';
                        }
                    ],
                    'c'    => [
                        'type'    => stringType(),
                        'resolve' => function () {
                            return 'Cherry';
                        }
                    ],
                    'deep' => [
                        'type'    => $type,
                        'resolve' => function () {
                            return [];
                        }
                    ]
                ];
            }
        ]);

        $schema = newSchema(['query' => $type]);

        /** @var ExecutionResult $executionResult */
        $executionResult = graphql($schema, $source);

        $this->assertSame([
            'data' => [
                'a'    => 'Apple',
                'b'    => 'Banana',
                'deep' => [
                    'b'      => 'Banana',
                    'deeper' => [
                        'b' => 'Banana',
                        'c' => 'Cherry'
                    ],
                    'c'      => 'Cherry',
                ],
                'c'    => 'Cherry'
            ]
        ], $executionResult);
    }

    /**
     * provides info about current execution state
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     */
    public function testProvidesInfoAboutCurrentExecutionState()
    {
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Test',
                'fields' => [
                    'test' => [
                        'type'    => stringType(),
                        'resolve' => function ($source, $args, $context, $_info) use (&$info) {
                            $info = $_info;
                        }
                    ]
                ]
            ]),
        ]);

        $rootValue = [
            'rootValue' => 'val'
        ];

        $ast = parse('query ($var: String) { result: test }');
        execute($schema, $ast, $rootValue, null, ['var' => 123]);

        /** @var ResolveInfo $info */
        $this->assertSame('test', $info->getFieldName());
        $this->assertSame(1, count($info->getFieldNodes()));
        $this->assertEquals(
            $ast->getDefinitions()[0]->getSelectionSet()->getSelections()[0],
            $info->getFieldNodes()[0]
        );
        $this->assertSame(stringType(), $info->getReturnType());
        $this->assertEquals($schema->getQueryType(), $info->getParentType());
        $this->assertSame(["result"], $info->getPath()); // { prev: undefined, key: 'result' }
        $this->assertEquals($schema, $info->getSchema());
        $this->assertEquals($rootValue, $info->getRootValue());
        $this->assertEquals($ast->getDefinitions()[0], $info->getOperation());
        $this->assertEquals(['var' => 123], $info->getVariableValues());
    }

    /**
     * Threads root value context correctly
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     */
    public function testThreadsRootValueContextCorrectly()
    {
        $resolvedRootValue = null;

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Test',
                'fields' => [
                    'a' => [
                        'type'    => stringType(),
                        'resolve' => function ($rootValue) use (&$resolvedRootValue) {
                            $resolvedRootValue = $rootValue;
                        }
                    ]
                ]
            ]),
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
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     */
    public function testCorrectlyThreadsArguments()
    {
        $resolvedArgs = null;

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'b' => [
                        'type'    => intType(),
                        'args'    => [
                            'numArg'    => ['type' => intType()],
                            'stringArg' => ['type' => stringType()]
                        ],
                        'resolve' => function ($source, $args) use (&$resolvedArgs) {
                            $resolvedArgs = $args;
                        }
                    ]
                ]
            ]),
        ]);

        execute($schema, parse('query Example { b(numArg: 123, stringArg: "foo") }'));

        $this->assertEquals(['numArg' => 123, 'stringArg' => 'foo'], $resolvedArgs);
    }

    public function testNullsOutErrorsSubTrees()
    {
        $source = dedent('{
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
        }');

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
                return new \React\Promise\Promise(function () {
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'sync'                => ['type' => stringType()],
                    'syncError'           => ['type' => stringType()],
                    'syncRawError'        => ['type' => stringType()],
                    'syncReturnError'     => ['type' => stringType()],
                    'syncReturnErrorList' => ['type' => newList(stringType())],
                    'async'               => ['type' => stringType()],
                    'asyncReject'         => ['type' => stringType()],
                    'asyncRawReject'      => ['type' => stringType()],
                    'asyncEmptyReject'    => ['type' => stringType()],
                    'asyncError'          => ['type' => stringType()],
                    'asyncRawError'       => ['type' => stringType()],
                    'asyncReturnError'    => ['type' => stringType()],
                ]
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema, parse($source), $data);

        $this->assertEquals([
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
                    'locations' => [
                        [
                            'line'   => 9,
                            'column' => 11,
                        ]
                    ],
                    'path'      => ['asyncRawReject']
                ],
                [
                    'message'   => '',
                    'locations' => [
                        [
                            'line'   => 10,
                            'column' => 11,
                        ]
                    ],
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
            ],
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
        ], $result->toArray());
    }

    public function testNullsErrorSubTreeForPromiseRejection()
    {
        $query = '
          query {
            foods {
              name
            }
          }
        ';

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'foods' => [
                        'type'    => newList(newObjectType([
                            'name'   => 'Food',
                            'fields' => [
                                'name' => [
                                    'type' => stringType()
                                ]
                            ]
                        ])),
                        'resolve' => function () {
                            return \React\Promise\reject(new \Exception('Dangit'));
                        }
                    ],
                ]
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
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

    public function testFullResponsePathIsIncludedForNonNullableFields()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $A = newObjectType([
            'name'   => 'A',
            'fields' => function () use (&$A) {
                return [
                    'nullableA' => [
                        'type'    => $A,
                        'resolve' => function () {
                            return [];
                        },
                    ],
                    'nonNullA'  => [
                        'type'    => newNonNull($A),
                        'resolve' => function () {
                            return [];
                        },
                    ],
                    'throws'    => [
                        'type'    => newNonNull(stringType()),
                        'resolve' => function () {
                            throw new \Exception('Catch me if you can!');
                        },
                    ],
                ];
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'query',
                'fields' => function () use (&$A) {
                    return [
                        'nullableA' => [
                            'type'    => $A,
                            'resolve' => function () {
                                return [];
                            },
                        ],
                    ];
                },
            ])
        ]);

        $query = dedent('
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
          }
        ');

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema, parse($query));

        $this->assertSame([
            'errors' => [
                [
                    'message'   => 'Catch me if you can!',
                    'locations' => [
                        [
                            'line'   => 6,
                            'column' => 1,
                        ],
                    ],
                    'path'      => [
                        'nullableA',
                        'aliasedA',
                        'nonNullA',
                        'anotherA',
                        'throws',
                    ]
                ]
            ],
            'data'   => [
                'nullableA' => [
                    'aliasedA' => null,
                ],
            ],
        ], $result->toArray());
    }

    /**
     * Uses the inline operation if no operation name is provided
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     */
    public function testUsesTheInlineOperationIfNoOperationIsProvided()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
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
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     */
    public function testUsesTheNamedOperationIfOperationNameIsProvided()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
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
     */
    public function testProvidesErrorIfNoOperationIsProvided()
    {
        $rootValue = ['a' => 'b'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
                    ]
                ]
            ])
        ]);

        $query  = 'fragment Example on Type { a }';

        /** @noinspection PhpUnhandledExceptionInspection */
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
     */
    public function testErrorsIfNoOpNameIsProvidedWithMultipleOperations()
    {
        $rootValue = ['a' => 'b'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
                    ]
                ]
            ])
        ]);

        $query  = 'query Example { a } query OtherExample { a }';

        /** @noinspection PhpUnhandledExceptionInspection */
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
     */
    public function testErrorsIfUnknownOperationNameIsProvided()
    {
        $rootValue = ['a' => 'b'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
                    ]
                ]
            ])
        ]);

        $query = 'query Example { a } query OtherExample { a }';

        /** @noinspection PhpUnhandledExceptionInspection */
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
     */
    public function testUsesTheQuerySchemaForQueries()
    {
        $rootValue = ['a' => 'b'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query'        => newObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
                    ]
                ]
            ]),
            'mutation'     => newObjectType([
                'name'   => 'M',
                'fields' => [
                    'c' => [
                        'type' => stringType(),
                    ]
                ]
            ]),
            'subscription' => newObjectType([
                'name'   => 'S',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
                    ]
                ]
            ])
        ]);

        $query = 'query Q { a } mutation M { c } subscription S { a }';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema, parse($query), $rootValue, [], [], 'Q');

        $this->assertEquals([
            'data' => [
                'a' => 'b',
            ]
        ], $result->toArray());
    }

    /**
     * Uses the mutation schema for mutations
     */
    public function testUsesTheMutationSchemaForMutations()
    {
        $rootValue = ['a' => 'b', 'c' => 'd'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query'    => newObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
                    ],
                ],
            ]),
            'mutation' => newObjectType([
                'name'   => 'M',
                'fields' => [
                    'c' => [
                        'type' => stringType(),
                    ],
                ],
            ]),
        ]);

        $query  = 'query Q { a } mutation M { c } subscription S { a }';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema, parse($query), $rootValue, [], [], 'M');

        $this->assertEquals([
            'data' => [
                'c' => 'd',
            ]
        ], $result->toArray());
    }

    /**
     * Uses the subscription schema for subscriptions
     */
    public function testUsesTheSubscriptionSchemaForSubscriptions()
    {
        $rootValue = ['a' => 'b'];

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query'        => newObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
                    ]
                ]
            ]),
            'subscription' => newObjectType([
                'name'   => 'S',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
                    ]
                ]
            ])
        ]);

        $query  = 'query Q { a } subscription S { a }';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema, parse($query), $rootValue, [], [], 'S');

        $this->assertEquals([
            'data' => [
                'a' => 'b',
            ]
        ], $result->toArray());
    }

    /**
     * Correct field ordering despite execution order
     */
    public function testCorrectFieldOrderingDespiteExecutionOrder()
    {
        $rootValue = [
            'a' => function () {
                return 'a';
            },
            'b' => function () {
                return \React\Promise\resolve('b');
            },
            'c' => function () {
                return 'c';
            },
            'd' => function () {
                return \React\Promise\resolve('d');
            },
            'e' => function () {
                return 'e';
            },
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => ['type' => stringType()],
                    'b' => ['type' => stringType()],
                    'c' => ['type' => stringType()],
                    'd' => ['type' => stringType()],
                    'e' => ['type' => stringType()],
                ],
            ]),
        ]);

        $query  = '{ a, b, c, d, e }';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema, parse($query), $rootValue);

        $this->assertEquals([
            'data' => [
                'a' => 'a',
                'b' => 'b',
                'c' => 'c',
                'd' => 'd',
                'e' => 'e',
            ],
        ], $result->toArray());
    }

    /**
     * Avoid recursions
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Language\SyntaxErrorException
     */
    public function testAvoidRecursions()
    {
        $rootValue = ['a' => 'b'];

        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Type',
                'fields' => [
                    'a' => [
                        'type' => stringType(),
                    ],
                ],
            ]),
        ]);

        $query  = '
        query Q {
          a
          ...Frag
          ...Frag
        }
    
        fragment Frag on Type {
          a
          ...Frag
        }
        ';

        $result = execute($schema, parse($query), $rootValue, [], [], 'Q');

        $this->assertEquals([
            'data' => [
                'a' => 'b',
            ],
        ], $result->toArray());
    }

    /**
     * Does not include illegal fields in output
     */
    public function testDoesNotIncludeIllegalFieldsInOutput()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query'    => newObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => ['type' => stringType()],
                ],
            ]),
            'mutation' => newObjectType([
                'name'   => 'M',
                'fields' => [
                    'c' => ['type' => stringType()],
                ],
            ]),
        ]);


        /** @noinspection PhpUnhandledExceptionInspection */
        $executionResult = execute($schema, parse('mutation M { thisIsIllegalDontIncludeMe }'));

        $expected = new ExecutionResult([], []);

        $this->assertEquals($expected, $executionResult);
    }


    /**
     * Fails when an isTypeOf check is not met
     */
    public function testFailsWhenAnIsTypeOfCheckIsNotMet()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $specialType = newObjectType([
            'name'     => 'specialType',
            'fields'   => [
                'value' => [
                    'type' => stringType()
                ]
            ],
            'isTypeOf' => function ($obj) {
                return $obj instanceof Special;
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => function () use ($specialType) {
                    return [
                        'specials' => [
                            'type'    => newList($specialType),
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

        $query = '{ specials { value } }';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema, parse($query), $rootValue);

        $this->assertSame([
            'errors' => [
                [
                    'message'   => 'Expected value of type "specialType" but got: Object.',
                    'locations' => [
                        [
                            'line'   => 1,
                            'column' => 3,
                        ],
                    ],
                    'path'      => ['specials', 1],
                ],
            ],
            'data'   => [
                'specials' => [
                    ['value' => 'foo'],
                    null,
                ],
            ],
        ], $result->toArray());
    }

    /**
     * Executes ignoring invalid non-executable definitions
     */
    public function testExecutesIgnoringInvalidNonExecutableDefinitions()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'foo' => [
                        'type' => stringType(),
                    ]
                ]
            ])
        ]);

        $query = '{ foo } type Query { bar: String }';

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema, parse($query));

        $this->assertEquals([
            'data' => [
                'foo' => null,
            ]
        ], $result->toArray());
    }

    /**
     * Uses a custom field resolver
     */
    public function testUsesACustomFieldResolver()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'foo' => [
                        'type' => stringType(),
                    ]
                ]
            ])
        ]);

        $query = '{ foo }';

        $customResolver = function ($source, $args, $context, ResolveInfo $info) {
            return $info->getFieldName();
        };

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = execute($schema, parse($query), [], [], [], null, $customResolver);

        $this->assertEquals([
            'data' => [
                'foo' => 'foo',
            ]
        ], $result->toArray());
    }

//    public function testNonNullFieldQuery()
//    {
//        /** @noinspection PhpUnhandledExceptionInspection */
//        $schema = newSchema([
//            'query' => newObjectType([
//                'name'   => 'Greeting',
//                'fields' => [
//                    'hello' => [
//                        'type'    => newNonNull(stringType()),
//                        'resolve' => function () {
//                            return null;
//                        }
//                    ]
//                ]
//            ])
//        ]);
//
//        /** @noinspection PhpUnhandledExceptionInspection */
//        $executionResult = graphql($schema, 'query Greeting {hello}');
//
//        $this->assertSame([
//            'errors' => [
//                [
//
//                    'message'   => 'Cannot return null for non-nullable field Greeting.hello.',
//                    'locations' => null,
//                    'path'      => null,
//                ]
//            ],
//            'data'   => null
//        ], $executionResult);
//    }
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
