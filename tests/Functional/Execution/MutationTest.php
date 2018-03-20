<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Schema;
use React\Promise\Promise;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class MutationTest extends TestCase
{

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testSimpleMutation()
    {
        $schema = GraphQLSchema([
            'mutation' =>
                new ObjectType([
                    'name'   => 'M',
                    'fields' => [
                        'greeting' => [
                            'type'    => GraphQLString(),
                            'resolve' => function ($source, $args, $context, $info) {
                                return sprintf('Hello %s.', $args['name']);
                            },
                            'args'    => [
                                'name' => [
                                    'type' => GraphQLString()
                                ]
                            ]
                        ]
                    ]
                ])
        ]);

        $source = '
        mutation M($name: String) {
            greeting(name:$name)
        }';

        $executionResult = execute($schema, parse($source), '', null, ['name' => 'Han Solo']);

        $expected = new ExecutionResult([
            'greeting' => 'Hello Han Solo.'
        ], []);

        $this->assertEquals($expected, $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testDoesNotIncludeIllegalFieldsInOutput()
    {
        $schema = GraphQLSchema([
            'query'    => new ObjectType([
                'name'   => 'Q',
                'fields' => [
                    'a' => ['type' => GraphQLString()],
                ]
            ]),
            'mutation' => new ObjectType([
                'name'   => 'M',
                'fields' => [
                    'c' => ['type' => GraphQLString()],
                ]
            ])
        ]);


        $executionResult = execute($schema, parse('mutation M { thisIsIllegalDontIncludeMe }'));

        $expected = new ExecutionResult([], []);

        $this->assertEquals($expected, $executionResult);
    }

    // EXECUTE: HANDLES MUTATION EXECUTION ORDERING

    /**
     * Evaluates mutations serially
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testEvaluatesMutationsSerially()
    {
        $source = 'mutation M {
          first: immediatelyChangeTheNumber(newNumber: 1) {
            theNumber
          },
          second: promiseToChangeTheNumber(newNumber: 2) {
            theNumber
          },
          third: immediatelyChangeTheNumber(newNumber: 3) {
            theNumber
          }
          fourth: promiseToChangeTheNumber(newNumber: 4) {
            theNumber
          },
          fifth: immediatelyChangeTheNumber(newNumber: 5) {
            theNumber
          }
        }';

        $result   = execute(rootSchema(), parse($source), new Root(6));
        $expected = [
            'data' => [
                'first'  => [
                    'theNumber' => 1
                ],
                'second' => [
                    'theNumber' => 2
                ],
                'third'  => [
                    'theNumber' => 3
                ],
                'fourth' => [
                    'theNumber' => 4
                ],
                'fifth'  => [
                    'theNumber' => 5
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    //evaluates mutations correctly in the presense of a failed mutation

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testEvaluatesMutationsCorrectlyInThePresenceOfAvailedMutation()
    {
        $source = 'mutation M {
          first: immediatelyChangeTheNumber(newNumber: 1) {
            theNumber
          },
          second: promiseToChangeTheNumber(newNumber: 2) {
            theNumber
          },
          third: failToChangeTheNumber(newNumber: 3) {
            theNumber
          }
          fourth: promiseToChangeTheNumber(newNumber: 4) {
            theNumber
          },
          fifth: immediatelyChangeTheNumber(newNumber: 5) {
            theNumber
          }
          sixth: promiseAndFailToChangeTheNumber(newNumber: 6) {
            theNumber
          }
        }';

        $result   = execute(rootSchema(), parse($source), new Root(6));
        $expected = [
            'data'   => [
                'first'  => [
                    'theNumber' => 1
                ],
                'second' => [
                    'theNumber' => 2
                ],
                'third'  => null,
                'fourth' => [
                    'theNumber' => 4
                ],
                'fifth'  => [
                    'theNumber' => 5
                ],
                'sixth'  => null
            ],
            'errors' => [
                [
                    'message'   => 'Cannot change the number',
                    'locations' => null,
                    'path'      => ['third']
                ],
                [
                    'message'   => 'Cannot change the number',
                    'locations' => null,
                    'path'      => null
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}


class NumberHolder
{
    public $theNumber;

    public function __construct($originalNumber)
    {
        $this->theNumber = $originalNumber;
    }
}

class Root
{
    public $numberHolder;

    public function __construct($originalNumber)
    {
        $this->numberHolder = new NumberHolder($originalNumber);
    }

    /**
     * @param $newNumber
     * @return NumberHolder
     */
    public function immediatelyChangeTheNumber($newNumber)
    {
        $this->numberHolder->theNumber = $newNumber;
        return $this->numberHolder;
    }

    /**
     * @param $newNumber
     *
     * @return Promise
     */
    public function promiseToChangeTheNumber($newNumber)
    {
        return new Promise(function (callable $resolve) use ($newNumber) {
            return $resolve($this->immediatelyChangeTheNumber($newNumber));
        });
    }

    /**
     * @throws \Exception
     */
    public function failToChangeTheNumber()
    {
        throw new ExecutionException('Cannot change the number');
    }

    /**
     * @return Promise
     */
    public function promiseAndFailToChangeTheNumber()
    {
        return new Promise(function (callable $resolve, callable $reject) {
            $reject(new ExecutionException("Cannot change the number"));
        });
    }
}

function rootSchema(): Schema
{
    $numberHolderType = GraphQLObjectType([
        'fields' => [
            'theNumber' => ['type' => GraphQLInt()],
        ],
        'name'   => 'NumberHolder',
    ]);

    $schema = GraphQLSchema([
        'query'    => GraphQLObjectType([
            'fields' => [
                'numberHolder' => ['type' => $numberHolderType],
            ],
            'name'   => 'Query',
        ]),
        'mutation' => GraphQLObjectType([
            'fields' => [
                'immediatelyChangeTheNumber'      => [
                    'type'    => $numberHolderType,
                    'args'    => ['newNumber' => ['type' => GraphQLInt()]],
                    'resolve' => function (Root $obj, $args) {
                        return $obj->immediatelyChangeTheNumber($args['newNumber']);
                    }
                ],
                'promiseToChangeTheNumber'        => [
                    'type'    => $numberHolderType,
                    'args'    => ['newNumber' => ['type' => GraphQLInt()]],
                    'resolve' => function (Root $obj, $args) {
                        return $obj->promiseToChangeTheNumber($args['newNumber']);
                    }
                ],
                'failToChangeTheNumber'           => [
                    'type'    => $numberHolderType,
                    'args'    => ['newNumber' => ['type' => GraphQLInt()]],
                    'resolve' => function (Root $obj, $args) {
                        return $obj->failToChangeTheNumber($args['newNumber']);
                    }
                ],
                'promiseAndFailToChangeTheNumber' => [
                    'type'    => $numberHolderType,
                    'args'    => ['newNumber' => ['type' => GraphQLInt()]],
                    'resolve' => function (Root $obj, $args) {
                        return $obj->promiseAndFailToChangeTheNumber($args['newNumber']);
                    }
                ]
            ],
            'name'   => 'Mutation',
        ])
    ]);

    return $schema;
}
