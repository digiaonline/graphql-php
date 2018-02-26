<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\Execution;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Test\Functional\Language\AbstractParserTest;
use Digia\GraphQL\Type\Definition\ObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class MutationTest extends AbstractParserTest
{

    /**
     * @throws \Exception
     */
    public function testSimpleMutation()
    {
        $schema = GraphQLSchema([
            'mutation' =>
                new ObjectType([
                    'name'   => 'greeting',
                    'fields' => [
                        ' message' => [
                            'type'    => GraphQLString(),
                            'resolve' => function ($name) {
                                return sprintf("Hello %s. Record was written to database", $name);
                            }
                        ]
                    ]
                ])
        ]);

        /** @var DocumentNode $documentNode */
        $documentNode = $this->parser->parse(new Source('
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
            'message' => 'Hello Han Solo. Record was written to database'
        ], []);

        $this->assertEquals($expected, $executionResult);
    }
}
