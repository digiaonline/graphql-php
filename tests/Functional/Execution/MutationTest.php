<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use function Digia\GraphQL\execute;
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
     * @throws \Digia\GraphQL\Error\InvariantException
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

        /** @var ExecutionResult $executionResult */
        $executionResult = execute($schema, $documentNode);

        $expected = new ExecutionResult([
            'greeting' => 'Hello Han Solo.'
        ], []);

        $this->assertEquals($expected, $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
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


        /** @var ExecutionResult $executionResult */
        $executionResult = execute($schema, $documentNode);

        $expected = new ExecutionResult([], []);

        $this->assertEquals($expected, $executionResult);
    }
}
