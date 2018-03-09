<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\Execution;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\ObjectType;
use function Digia\GraphQL\parse;
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
                            'type'    => GraphQLString(),
                            'resolve' => function ($source, $args, $context, $info) {
                                return sprintf('Hello %s.', 'Han Solo');
                            },
                        ]
                    ]
                ])
        ]);

        //@TODO Get proper $name variable

        /** @var DocumentNode $documentNode */
        $documentNode = parse('
        mutation M($name: String) {
            greeting(name:$name)
        }
        ');

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
            'greeting' => 'Hello Han Solo.'
        ], []);

        $this->assertEquals($expected, $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Exception
     */
    public function testDoesNotIncludeIllegalFieldsInOutput()
    {
        /** @var DocumentNode $documentNode */
        $documentNode = parse('
        mutation M {
          thisIsIllegalDontIncludeMe
        }
        ');

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
