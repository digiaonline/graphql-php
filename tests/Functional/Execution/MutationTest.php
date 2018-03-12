<?php

namespace Digia\GraphQL\Test\Functional\Execution;


use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\ObjectType;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class MutationTest extends TestCase
{

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
}
