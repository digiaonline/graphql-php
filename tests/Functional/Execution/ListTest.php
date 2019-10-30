<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\intType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;

class ListTest extends TestCase
{
    // EXECUTE: ACCEPTS ANY ITERABLE AS LIST VALUE

    /**
     * @param TypeInterface $testType
     * @param mixed         $testData
     * @param mixed         $expected
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function makeTest(TypeInterface $testType, $testData, $expected)
    {
        $dataType = newObjectType([
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

        $schema = newSchema(['query' => $dataType]);


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
        $this->makeTest(newList(stringType()),
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
        $this->makeTest(newList(stringType()),
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
        $this->makeTest(newList(stringType()),
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
        $this->makeTest(newList(intType()),
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
        $this->makeTest(newList(intType()),
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
        $this->makeTest(newList(intType()),
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
        $this->makeTest(newList(intType()),
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
        $this->makeTest(newList(intType()),
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
        $this->makeTest(newList(intType()),
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
        $this->makeTest(newList(intType()),
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
        $this->makeTest(newList(intType()),
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
        $this->makeTest(newList(intType()),
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
        $this->makeTest(newList(intType()),
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
