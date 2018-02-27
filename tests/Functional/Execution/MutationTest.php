<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\Execution;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use function Digia\GraphQL\Language\parser;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\ObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class MutationTest extends TestCase
{

    /**
     * @throws \Exception
     */
    public function testSimpleMutation()
    {
        $schema = GraphQLSchema([
            'mutation' =>
                new ObjectType([
                    'name'   => 'M',
                    'fields' => [
                        'greeting' => [
                            'type'    => new ObjectType([
                                'name' => 'GreetingType',
                                'fields' => [
                                    'message' => [
                                        'type' => GraphQLString(),
                                    ]
                                ],
                            ]),
                            'resolve' => function ($name) {
                                return [
                                    'message' => sprintf('Hello %s.', $name)
                                ];
                            }
                        ]
                    ]
                ])
        ]);

        /** @var DocumentNode $documentNode */
        $documentNode = parser()->parse(new Source('
        mutation M{
            greeting(name:"Han Solo") {
               message   
            }
        }
        '));

        $rootValue      = [];
        $contextValue   = '';
        $variableValues = [];
        $operationName  = 'M';
        $fieldResolver  = null;

        /** @var ExecutionResult $executionResult */
        $executionResult = Execution::execute(
            $schema,
            $documentNode,
            $rootValue,
            $contextValue,
            $variableValues,
            $operationName,
            $fieldResolver
        );

        $expected = new ExecutionResult([
            'greeting' => [
                'message' => 'Hello Han Solo.'
            ]
        ], []);

        $this->assertEquals($expected, $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testDoesNotIncludeIllegalFieldsInOutput()
    {
        /** @var DocumentNode $documentNode */
        $documentNode = parser()->parse(new Source('
        mutation M {
          thisIsIllegalDontIncludeMe
        }'
        ));

        $schema = GraphQLSchema([
            'mutation' =>
                new ObjectType([
                    'name'   => 'M',
                    'fields' => [
                        'd' => [
                            'type'    => GraphQLString(),
                            'resolve' => function () {
                                return 'd';
                            }
                        ]
                    ]
                ])
        ]);

        $schema = GraphQLSchema([
            'query' => new ObjectType([
                'name' => 'Q',
                'fields' => [
                    'a' => ['type' => GraphQLString()],
                ]
            ]),
            'mutation' => new ObjectType([
                'name' => 'M',
                'fields' => [
                    'c' => ['type' => GraphQLString()],
                ]
            ])
        ]);


        $rootValue      = [];
        $contextValue   = '';
        $variableValues = [];
        $operationName  = 'M';
        $fieldResolver  = null;


        /** @var ExecutionResult $executionResult */
        $executionResult = Execution::execute(
            $schema,
            $documentNode,
            $rootValue,
            $contextValue,
            $variableValues,
            $operationName,
            $fieldResolver
        );

        $expected = new ExecutionResult([], []);

        $this->assertEquals($expected, $executionResult);
    }
}
