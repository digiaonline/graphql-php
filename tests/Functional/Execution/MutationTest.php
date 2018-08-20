<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Test\TestCase;
use React\Promise\Promise;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\intType;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;

class MutationTest extends TestCase
{

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testSimpleMutation()
    {
        $schema = newSchema([
            'mutation' =>
                newObjectType([
                    'name'   => 'M',
                    'fields' => [
                        'greeting' => [
                            'type'    => stringType(),
                            'resolve' => function ($source, $args, $context, $info) {
                                return sprintf('Hello %s.', $args['name']);
                            },
                            'args'    => [
                                'name' => [
                                    'type' => stringType()
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

    //evaluates mutations correctly in the presence of a failed mutation

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
                    'path'      => ['sixth']
                ]
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}


class NumberHolder
{
    /**
     * @var int
     */
    public $theNumber;

    /**
     * NumberHolder constructor.
     * @param int $originalNumber
     */
    public function __construct(int $originalNumber)
    {
        $this->theNumber = $originalNumber;
    }
}

class Root
{
    public $numberHolder;

    /**
     * Root constructor.
     * @param int $originalNumber
     */
    public function __construct(int $originalNumber)
    {
        $this->numberHolder = new NumberHolder($originalNumber);
    }

    /**
     * @param int $newNumber
     * @return NumberHolder
     */
    public function immediatelyChangeTheNumber(int $newNumber)
    {
        $this->numberHolder->theNumber = $newNumber;
        return $this->numberHolder;
    }

    /**
     * @param int $newNumber
     *
     * @return Promise
     */
    public function promiseToChangeTheNumber(int $newNumber)
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
    $numberHolderType = newObjectType([
        'fields' => [
            'theNumber' => ['type' => intType()],
        ],
        'name'   => 'NumberHolder',
    ]);

    $schema = newSchema([
        'query'    => newObjectType([
            'fields' => [
                'numberHolder' => ['type' => $numberHolderType],
            ],
            'name'   => 'Query',
        ]),
        'mutation' => newObjectType([
            'fields' => [
                'immediatelyChangeTheNumber'      => [
                    'type'    => $numberHolderType,
                    'args'    => ['newNumber' => ['type' => intType()]],
                    'resolve' => function (Root $obj, $args) {
                        return $obj->immediatelyChangeTheNumber($args['newNumber']);
                    }
                ],
                'promiseToChangeTheNumber'        => [
                    'type'    => $numberHolderType,
                    'args'    => ['newNumber' => ['type' => intType()]],
                    'resolve' => function (Root $obj, $args) {
                        return $obj->promiseToChangeTheNumber($args['newNumber']);
                    }
                ],
                'failToChangeTheNumber'           => [
                    'type'    => $numberHolderType,
                    'args'    => ['newNumber' => ['type' => intType()]],
                    'resolve' => function (Root $obj) {
                        return $obj->failToChangeTheNumber();
                    }
                ],
                'promiseAndFailToChangeTheNumber' => [
                    'type'    => $numberHolderType,
                    'args'    => ['newNumber' => ['type' => intType()]],
                    'resolve' => function (Root $obj) {
                        return $obj->promiseAndFailToChangeTheNumber();
                    }
                ]
            ],
            'name'   => 'Mutation',
        ])
    ]);

    return $schema;
}
