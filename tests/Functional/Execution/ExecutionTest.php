<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Execution\Execution;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\AST\Node\NameNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\Location;
use function Digia\GraphQL\Language\parse;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Language\SourceLocation;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\Language\parser;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLString;

class ExecutionTest extends TestCase
{

    /**
     * @throws \Exception
     */
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
                    'kind'                => NodeKindEnum::OPERATION_DEFINITION,
                    'name'                => new NameNode([
                        'value' => 'query',
                    ]),
                    'selectionSet'        => new SelectionSetNode([
                        'selections' => [
                            new FieldNode([
                                'name'      => new NameNode([
                                    'value'    => 'hello',
                                    'location' => new Location(
                                        15,
                                        20,
                                        new Source('query Example {hello}', 'GraphQL', new SourceLocation())
                                    )
                                ]),
                                'arguments' => []
                            ])
                        ]
                    ]),
                    'operation'           => 'query',
                    'directives'          => [],
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

    /**
     * @throws \Exception
     */
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
                    'kind'                => NodeKindEnum::OPERATION_DEFINITION,
                    'name'                => new NameNode([
                        'value' => 'query'
                    ]),
                    'selectionSet'        => new SelectionSetNode([
                        'selections' => [
                            new FieldNode([
                                'name'      => new NameNode([
                                    'value'    => 'greeting',
                                    'location' => new Location(
                                        15,
                                        20,
                                        new Source('query Hello($name: String) {greeting(name: $name)}', 'GraphQL',
                                            new SourceLocation())
                                    )
                                ]),
                                'arguments' => [
                                    new InputValueDefinitionNode([
                                        'name'         => new NameNode([
                                            'value' => 'name'
                                        ]),
                                        'type'         => GraphQLString(),
                                        'defaultValue' => new StringValueNode([
                                            'value' => 'Han Solo',
                                        ]),
                                    ])
                                ]
                            ])
                        ]
                    ]),
                    'operation'           => 'query',
                    'directives'          => [],
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
     * @throws \Exception
     */
    public function testExecuteQueryWithMultipleFields()
    {
        $schema = new Schema([
            'query' =>
                new ObjectType([
                    'name'   => 'Human',
                    'fields' => [
                        'id'         => [
                            'type'    => GraphQLInt(),
                            'resolve' => function () {
                                return 1000;
                            }
                        ],
                        'type'       => [
                            'type'    => GraphQLString(),
                            'resolve' => function () {
                                return 'Human';
                            }
                        ],
                        'friends'    => [
                            'type'    => GraphQLList(GraphQLString()),
                            'resolve' => function () {
                                return ['1002', '1003', '2000', '2001'];
                            }
                        ],
                        'appearsIn'  => [
                            'type'    => GraphQLList(GraphQLInt()),
                            'resolve' => function () {
                                return [4, 5, 6];
                            }
                        ],
                        'homePlanet' => [
                            'type'    => GraphQLString(),
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
                    'kind'                => NodeKindEnum::OPERATION_DEFINITION,
                    'name'                => new NameNode([
                        'value' => 'query'
                    ]),
                    'selectionSet'        => new SelectionSetNode([
                        'selections' => [
                            new FieldNode([
                                'name' => new NameNode([
                                    'value'    => 'id',
                                    'location' => new Location(
                                        15,
                                        20,
                                        new Source('query Human {id, type, friends, appearsIn, homePlanet}', 'GraphQL',
                                            new SourceLocation())
                                    )
                                ]),
                            ]),
                            new FieldNode([
                                'name' => new NameNode([
                                    'value'    => 'type',
                                    'location' => new Location(
                                        15,
                                        20,
                                        new Source('query Human {id, type, friends, appearsIn, homePlanet}', 'GraphQL',
                                            new SourceLocation())
                                    )
                                ]),
                            ]),
                            new FieldNode([
                                'name' => new NameNode([
                                    'value'    => 'friends',
                                    'location' => new Location(
                                        15,
                                        20,
                                        new Source('query Human {id, type, friends, appearsIn, homePlanet}', 'GraphQL',
                                            new SourceLocation())
                                    )
                                ]),
                            ]),
                            new FieldNode([
                                'name' => new NameNode([
                                    'value'    => 'appearsIn',
                                    'location' => new Location(
                                        15,
                                        20,
                                        new Source('query Human {id, type, friends, appearsIn, homePlanet}', 'GraphQL',
                                            new SourceLocation())
                                    )
                                ]),
                            ]),
                            new FieldNode([
                                'name' => new NameNode([
                                    'value'    => 'homePlanet',
                                    'location' => new Location(
                                        15,
                                        20,
                                        new Source('query Human {id, type, friends, appearsIn, homePlanet}', 'GraphQL',
                                            new SourceLocation())
                                    )
                                ]),
                            ])
                        ]
                    ]),
                    'operation'           => 'query',
                    'directives'          => [],
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
            'appearsIn'  => [4, 5, 6],
            'homePlanet' => 'Tatooine'
        ], []);

        $this->assertEquals($expected, $executionResult);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testHandleFragments()
    {
        $documentNode = parse('
      { a, ...FragOne, ...FragTwo }

      fragment FragOne on Type {
        b
        deep { b, deeper: deep { b } }
      }

      fragment FragTwo on Type {
        c
        deep { c, deeper: deep { c } }
      }');

        $Type = new ObjectType([
            'name'   => 'Type',
            'fields' => function () use (&$Type) {
                return [
                    'a'    => [
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Apple';
                        }
                    ],
                    'b'    => [
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Banana';
                        }
                    ],
                    'c'    => [
                        'type'    => GraphQLString(),
                        'resolve' => function () {
                            return 'Cherry';
                        }
                    ],
                    'deep' => [
                        'type'    => $Type,
                        'resolve' => function () {
                            return [];
                        }
                    ]
                ];
            }
        ]);

        $schema = new Schema([
            'query' => $Type
        ]);

        $rootValue      = [];
        $contextValue   = '';
        $variableValues = [];
        $operationName  = '';
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
            'a'    => 'Apple',
            'b'    => 'Banana',
            'c'    => 'Cherry',
            'deep' => [
//                'b'      => 'Banana',
//                'c'      => 'Cherry',
//                'deeper' => [
//                    'b' => 'Banana',
//                    'c' => 'Cherry'
//                ]
            ]
        ], []);

        $this->assertEquals($expected, $executionResult);
    }
}
