<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\newGraphQLList;
use function Digia\GraphQL\Type\newGraphQLObjectType;
use function Digia\GraphQL\Type\newGraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class ListTest extends TestCase
{
    // EXECUTE: ACCEPTS ANY ITERABLE AS LIST VALUE

    /**
     * @param $testType
     * @param $testData
     * @param $expected
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function makeTest($testType, $testData, $expected)
    {
        $dataType = newGraphQLObjectType([
            'name'   => 'DataType',
            'fields' => function () use (&$dataType, $testType, $testData) {
                return [
                    'test' => [
                        'type' => $testType
                    ],
                    'nest' => [
                        'type'    => $dataType,
                        'resolve' => function () use ($testData) {
                            return [
                                'test' => $testData
                            ];
                        }
                    ]
                ];
            }
        ]);

        $source = '{ nest { test } }';

        $schema = newGraphQLSchema(['query' => $dataType]);


        /** @var ExecutionResult $executionResult */
        $result = execute($schema, parse($source));

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAcceptAnArrayObjectAsListValue()
    {
        $this->makeTest(newGraphQLList(GraphQLString()),
            new \ArrayObject(['apple', 'banana', 'coconut']),
            [
                'data' => [
                    'nest' => [
                        'test' => ['apple', 'banana', 'coconut']
                    ]
                ]
            ]
        );

    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAcceptsAGeneratorAsFunctionValue()
    {
        $this->makeTest(newGraphQLList(GraphQLString()),
            function () {
                yield 'one';
                yield 2;
                yield true;
            },
            [
                'data' => [
                    'nest' => [
                        'test' => ['one', '2', 'true']
                    ]
                ]
            ]
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testDoesNotAcceptStringAsIterable()
    {
        $this->makeTest(newGraphQLList(GraphQLString()),
            'Singluar',
            [
                'data'   => [
                    'nest' => [
                        'test' => null
                    ]
                ]
                ,
                'errors' => [
                    [
                        'message'   => 'Expected Array or Traversable, but did not find one for field DataType.test.',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 10
                            ]
                        ],
                        'path'      => ['nest', 'test']
                    ]
                ]
            ]
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testArrayContainsValues()
    {
        $this->makeTest(newGraphQLList(GraphQLInt()),
            [1, 2],
            [
                'data' => [
                    'nest' => [
                        'test' => [1, 2]
                    ]
                ]
            ]
        );
    }


    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testArrayContainsNull()
    {
        $this->makeTest(newGraphQLList(GraphQLInt()),
            [1, null, 2],
            [
                'data' => [
                    'nest' => [
                        'test' => [1, null, 2]
                    ]
                ]
            ]
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testArrayReturnsNull()
    {
        $this->makeTest(newGraphQLList(GraphQLInt()),
            null,
            [
                'data' => [
                    'nest' => [
                        'test' => null
                    ]
                ]
            ]
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testPromiseContainsValues()
    {
        $this->makeTest(newGraphQLList(GraphQLInt()),
            \React\Promise\resolve([1, 2]),
            [
                'data' => [
                    'nest' => [
                        'test' => [1, 2]
                    ]
                ]
            ]
        );
    }


    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testPromiseContainsNull()
    {
        $this->makeTest(newGraphQLList(GraphQLInt()),
            \React\Promise\resolve([1, null, 2]),
            [
                'data' => [
                    'nest' => [
                        'test' => [1, null, 2]
                    ]
                ]
            ]
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testPromiseReturnsNull()
    {
        $this->makeTest(newGraphQLList(GraphQLInt()),
            \React\Promise\resolve(null),
            [
                'data' => [
                    'nest' => [
                        'test' => null
                    ]
                ]
            ]
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testPromiseReject()
    {
        $this->makeTest(newGraphQLList(GraphQLInt()),
            \React\Promise\reject(new \Exception('Bad')),
            [
                'data'   => [
                    'nest' => [
                        'test' => null
                    ]
                ],
                'errors' => [
                    [
                        'message'   => 'Bad',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 10
                            ]
                        ],
                        'path'      => ['nest', 'test']
                    ]
                ]
            ]
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testArrayPromiseContainsValues()
    {
        $this->makeTest(newGraphQLList(GraphQLInt()),
            [
                \React\Promise\resolve(1),
                \React\Promise\resolve(2)
            ],
            [
                'data' => [
                    'nest' => [
                        'test' => [1, 2]
                    ]
                ]
            ]
        );
    }


    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testArrayPromiseContainsNull()
    {
        $this->makeTest(newGraphQLList(GraphQLInt()),
            [
                \React\Promise\resolve(1),
                \React\Promise\resolve(null),
                \React\Promise\resolve(2)
            ],
            [
                'data' => [
                    'nest' => [
                        'test' => [1, null, 2]
                    ]
                ]
            ]
        );
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testArrayPromiseContainsReject()
    {
        $this->makeTest(newGraphQLList(GraphQLInt()),
            [
                \React\Promise\resolve(1),
                \React\Promise\reject(new \Exception('Bad')),
                \React\Promise\resolve(2)
            ],
            [
                'data'   => [
                    'nest' => [
                        'test' => [1, null, 2]
                    ]
                ],
                'errors' => [
                    [
                        'message'   => 'Bad',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 10
                            ]
                        ],
                        'path'      => ['nest', 'test', 1]
                    ]
                ]
            ]
        );
    }
}
