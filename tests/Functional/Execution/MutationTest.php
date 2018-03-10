<?php

namespace Digia\GraphQL\Test\Functional\Execution;


use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\ObjectType;
use function Digia\GraphQL\graphql;
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

        /** @var ExecutionResult $executionResult */
        $executionResult = graphql($schema, $source, '', null, ['name' => 'Han Solo']);

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
        $executionResult = graphql($schema, 'mutation M { thisIsIllegalDontIncludeMe }');

        $expected = new ExecutionResult([], []);

        $this->assertEquals($expected, $executionResult);
    }
}
