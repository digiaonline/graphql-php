<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLList;
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
}
