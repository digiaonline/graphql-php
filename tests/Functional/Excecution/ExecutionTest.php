<?php

namespace Digia\GraphQL\Test\Functional\Excecution;

use Digia\GraphQL\Execution\Execution;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;
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
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLList;
use Digia\GraphQL\Type\Schema\Schema;
use function Digia\GraphQL\Type\GraphQLString;

class ExecutionTest extends TestCase
{
    public function testExecuteHelloQuery()
    {
        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Greeting',
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
                                'arguments' => []
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

    public function testExecuteQueryHelloWithArgs()
    {
        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Greeting',
                    'fields' => [
                        'greeting' => [
                            'type'    => GraphQLString(),
                            'resolve' => function ($name) {
                                return sprintf('Hello %s', $name);
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
                                    'value' => 'greeting',
                                    'location' => new Location(
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Source('query Hello($name: String) {greeting(name: $name)}', 'GraphQL', 1)
                                    )
                                ]),
                                'arguments' => [
                                    new InputValueDefinitionNode([
                                        'name' => new NameNode([
                                            'value' => 'name'
                                        ]),
                                        'type' => GraphQLString(),
                                        'defaultValue' => 'Han Solo'
                                    ])
                                ]
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

        $expected = new ExecutionResult(['greeting' => 'Hello Han Solo'], []);

        $this->assertEquals($expected, $executionResult);
    }

    /**
     * @throws \TypeError
     */
    public function testExecuteQueryWithMultipleFields()
    {
        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Human',
                    'fields' => [
                        'id' =>  [
                            'type'    => GraphQLInt(),
                            'resolve' => function () {
                                return 1000;
                            }
                        ],
                        'type' => [
                            'type'    => GraphQLString(),
                            'resolve' => function () {
                                return 'Human';
                            }
                        ],
                        'friends' => [
                            'type'    => GraphQLList(GraphQLString()),
                            'resolve' => function () {
                                return ['1002', '1003', '2000', '2001'];
                            }
                        ],
                        'appearsIn' => [
                            'type' => GraphQLList(GraphQLInt()),
                            'resolve' => function () {
                                return [4, 5, 6];
                            }
                        ],
                        'homePlanet' => [
                            'type' => GraphQLString(),
                            'resolve' => function () {
                                return 'Tatooine';
                            }
                        ],
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
                                    'value' => 'id',
                                    'location' => new Location(
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Source('query Human {id, type, friends, appearsIn, homePlanet}', 'GraphQL', 1)
                                    )
                                ]),
                            ]),
                            new FieldNode([
                                'name' => new NameNode([
                                    'value' => 'type',
                                    'location' => new Location(
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Source('query Human {id, type, friends, appearsIn, homePlanet}', 'GraphQL', 1)
                                    )
                                ]),
                            ]),
                            new FieldNode([
                                'name' => new NameNode([
                                    'value' => 'friends',
                                    'location' => new Location(
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Source('query Human {id, type, friends, appearsIn, homePlanet}', 'GraphQL', 1)
                                    )
                                ]),
                            ]),
                            new FieldNode([
                                'name' => new NameNode([
                                    'value' => 'appearsIn',
                                    'location' => new Location(
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Source('query Human {id, type, friends, appearsIn, homePlanet}', 'GraphQL', 1)
                                    )
                                ]),
                            ]),
                            new FieldNode([
                                'name' => new NameNode([
                                    'value' => 'homePlanet',
                                    'location' => new Location(
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Token(TokenKindEnum::NAME, 15, 20, 1),
                                        new Source('query Human {id, type, friends, appearsIn, homePlanet}', 'GraphQL', 1)
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

        $expected = new ExecutionResult([
            'id'         => 1000,
            'type'       => 'Human',
            'friends'    => ['1002', '1003', '2000', '2001'],
            'appearsIn'  => [4,5,6],
            'homePlanet' => 'Tatooine'
        ], []);

        $this->assertEquals($expected, $executionResult);
    }
}
