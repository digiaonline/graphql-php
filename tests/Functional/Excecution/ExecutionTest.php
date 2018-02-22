<?php

namespace Digia\GraphQL\Test\Functional\Excecution;

use Digia\GraphQL\Execution\Execution;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\NameNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Schema\Schema;
use function Digia\GraphQL\Type\GraphQLString;

class ExecutionTest extends TestCase
{
    public function testExecuteSchema()
    {
        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Type',
                    'fields' => [
                        'hello' => [
                            'type'    => GraphQLString(),
                            'resolve' => function () {
                                return 'world';
                            }
                        ]
                    ]
                ])
        ]);

        $documentNode = new DocumentNode([
            'definitions' => [
                new OperationDefinitionNode([
                    'kind' => NodeKindEnum::OPERATION_DEFINITION,
                    'name' => new NameNode([
                        'value' => 'query'
                    ]),
                    'selectionSet' => new SelectionSetNode([
                        'selections' => [
                            new FieldNode([
                                'name' => new NameNode([
                                    'value' => 'hello',
                                    'location' => new Location(
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Source('query Example {hello}', 'GraphQL', 1)
                                    )
                                ]),
                            ])
                        ]
                    ]),
                    'operation' => 'query',
                    'directives' => [],
                    'variableDefinitions' => []
                ])
            ],
        ]);

        $rootValue      = [];
        $contextValue   = '';
        $variableValues = [];
        $operationName  = 'query';
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

        $expected = new ExecutionResult(['hello' => 'world'], []);

        $this->assertEquals($expected, $executionResult);
    }
}
