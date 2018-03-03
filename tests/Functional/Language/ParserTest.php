<?php

namespace Digia\GraphQL\Test\Functional\Language\AST;

use Digia\GraphQL\Error\SyntaxError;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NameNode;
use Digia\GraphQL\Language\AST\Node\NullValueNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\parseType;
use function Digia\GraphQL\parseValue;

class ParserTest extends TestCase
{

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParseProvidesUsefulErrors()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Expected Name, found <EOF>');
        parse('{');

        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Expected {, found <EOF>');
        parse('query', 'MyQuery.graphql');
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesVariableInlineValues()
    {
        parse('{ field(complex: { a: { b: [ $var ] } }) }');
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesConstantDefaultValues()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Unexpected $');
        parse('query Foo($x: Complex = { a: { b: [ $var ] } }) { field }');
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testDoesNotAcceptFragmentsNamedOn()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Unexpected Name "on"');
        parse('fragment on on on { on }');
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testDoesNotAcceptFragmentSpreadOfOn()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Expected Name, found }');
        parse('{ ...on }');
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesMultiByteCharacters()
    {
        /** @var DocumentNode $node */
        $node = parse(new Source('
          # This comment has a \u0A0A multi-byte character.
          { field(arg: "Has a \u0A0A multi-byte character.") }
        '));

        $this->assertArraySubset([
            'definitions' => [
                [
                    'selectionSet' => [
                        'selections' => [
                            [
                                'arguments' => [
                                    [
                                        'value' => [
                                            'kind' => NodeKindEnum::STRING,
                                            // TODO: Fix this test case.
                                            // 'value' => 'Has a \u0A0A multi-byte character.',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $node->toArray());
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testMultipleFragments()
    {
        $node = parse(new Source('
          { a, ...FragOne, ...FragTwo }
          fragment FragOne on Type {
            b
            deep { b, deeper: deep { b } }
          }
          fragment FragTwo on Type {
            c
            deep { c, deeper: deep { c } }
          }
        '));

        $expected = new DocumentNode([
            'location'     => new Location(0, 261, null),
            'definitions' => [
                new OperationDefinitionNode([
                    'name' => null,
                    'location' => new Location(11, 40),
                    'selectionSet' => new SelectionSetNode([
                        'location' => new Location(11, 40, null),
                        'selections' => [
                            new FieldNode([
                                'name'         => new NameNode([
                                    'value'    => 'a',
                                    'location' => new Location(13, 14, null)
                                ]),
                                'location'     => new Location(13, 14, null),
                                'alias'        => null,
                                'directives'   => [],
                                'arguments'    => [],
                                'selectionSet' => null
                            ]),
                            new FragmentSpreadNode([
                                'name' => new NameNode([
                                    'value'    => 'FragOne',
                                    'location' => new Location(19, 26, null)
                                ]),
                                'location' => new Location(16, 26, null),
                                'directives'   => [],
                                'selectionSet' => null
                            ]),
                            new FragmentSpreadNode([
                                'name' => new NameNode([
                                    'value'    => 'FragTwo',
                                    'location' => new Location(31, 38, null)
                                ]),
                                'location' => new Location(28, 38, null),
                                'directives'   => [],
                                'selectionSet' => null
                            ])
                        ]
                    ]),
                    'operation' => 'query',
                    'directives' => [],
                    'variableDefinitions' => []
                ]),
                new FragmentSpreadNode([
                    'name'         => new NameNode([
                        'value'    => 'FragOne',
                        'location' => new Location(60, 67, null)
                    ]),
                    'location' => new Location(51, 146),
                    'typeCondition' => new NamedTypeNode([
                        'name' => new NameNode([
                            'value' => 'Type',
                            'location' => new Location(71, 75),
                        ]),
                        'location' => new Location(71, 75),
                    ]),
                    'directives' => [],
                    'selectionSet' => new SelectionSetNode([
                        'location' => new Location(76, 146, null),
                        'selections' => [
                            new FieldNode([
                                'name'         => new NameNode([
                                    'value'    => 'b',
                                    'location' => new Location(90, 91, null)
                                ]),
                                'location'     => new Location(90, 91, null),
                                'alias'        => null,
                                'directives'   => [],
                                'arguments'    => [],
                                'selectionSet' => null
                            ]),
                            new FieldNode([
                                'name'         => new NameNode([
                                    'value'    => 'deep',
                                    'location' => new Location(104, 108, null)
                                ]),
                                'location'     => new Location(104, 134, null),
                                'alias'        => null,
                                'directives'   => [],
                                'arguments'    => [],
                                'selectionSet' => new SelectionSetNode([
                                    'location' => new Location(109, 134, null),
                                    'selections' => [
                                        new FieldNode([
                                            'name'         => new NameNode([
                                                'value'    => 'b',
                                                'location' => new Location(111, 112, null)
                                            ]),
                                            'location'     => new Location(111, 112, null),
                                            'alias'        => null,
                                            'directives'   => [],
                                            'arguments'    => [],
                                            'selectionSet' => null
                                        ]),
                                        new FieldNode([
                                            'name'         => new NameNode([
                                                'value'    => 'deep',
                                                'location' => new Location(122, 126, null)
                                            ]),
                                            'location'     => new Location(114, 132, null),
                                            'alias'        => new NameNode([
                                                'value' => 'deeper',
                                                'location' => new Location(114, 120, null)
                                            ]),
                                            'directives'   => [],
                                            'arguments'    => [],
                                            'selectionSet' => new SelectionSetNode([
                                                'location' => new Location(127, 132, null),
                                                'selections' => [
                                                    new FieldNode([
                                                        'name'         => new NameNode([
                                                            'value'    => 'b',
                                                            'location' => new Location(129, 130, null)
                                                        ]),
                                                        'location'     => new Location(129, 130, null),
                                                        'alias'        => null,
                                                        'directives'   => [],
                                                        'arguments'    => [],
                                                        'selectionSet' => null
                                                    ]),
                                                ]
                                            ])
                                        ]),
                                    ]
                                ])
                            ]),
                        ]
                    ]),
                ]),
                new FragmentSpreadNode([
                    'name'         => new NameNode([
                        'value'    => 'FragTwo',
                        'location' => new Location(166, 173, null)
                    ]),
                    'location' => new Location(157, 252),
                    'typeCondition' => new NamedTypeNode([
                        'name' => new NameNode([
                            'value' => 'Type',
                            'location' => new Location(177, 181),
                        ]),
                        'location' => new Location(177, 181),
                    ]),
                    'directives' => [],
                    'selectionSet' => new SelectionSetNode([
                        'location' => new Location(182, 252, null),
                        'selections' => [
                            new FieldNode([
                                'name'         => new NameNode([
                                    'value'    => 'c',
                                    'location' => new Location(196, 197, null)
                                ]),
                                'location'     => new Location(196, 197, null),
                                'alias'        => null,
                                'directives'   => [],
                                'arguments'    => [],
                                'selectionSet' => null
                            ]),
                            new FieldNode([
                                'name'         => new NameNode([
                                    'value'    => 'deep',
                                    'location' => new Location(210, 214, null)
                                ]),
                                'location'     => new Location(210, 240, null),
                                'alias'        => null,
                                'directives'   => [],
                                'arguments'    => [],
                                'selectionSet' => new SelectionSetNode([
                                    'location' => new Location(215, 240, null),
                                    'selections' => [
                                        new FieldNode([
                                            'name'         => new NameNode([
                                                'value'    => 'c',
                                                'location' => new Location(217, 218, null)
                                            ]),
                                            'location'     => new Location(217, 218, null),
                                            'alias'        => null,
                                            'directives'   => [],
                                            'arguments'    => [],
                                            'selectionSet' => null
                                        ]),
                                        new FieldNode([
                                            'name'         => new NameNode([
                                                'value'    => 'deep',
                                                'location' => new Location(228, 232, null)
                                            ]),
                                            'location'     => new Location(220, 238, null),
                                            'alias'        => new NameNode([
                                                'value' => 'deeper',
                                                'location' => new Location(220, 226, null)
                                            ]),
                                            'directives'   => [],
                                            'arguments'    => [],
                                            'selectionSet' => new SelectionSetNode([
                                                'location' => new Location(233, 238, null),
                                                'selections' => [
                                                    new FieldNode([
                                                        'name'         => new NameNode([
                                                            'value'    => 'c',
                                                            'location' => new Location(235, 236, null)
                                                        ]),
                                                        'location'     => new Location(235, 236, null),
                                                        'alias'        => null,
                                                        'directives'   => [],
                                                        'arguments'    => [],
                                                        'selectionSet' => null
                                                    ]),
                                                ]
                                            ])
                                        ]),
                                    ]
                                ])
                            ]),
                        ]
                    ]),
                ])
            ]
        ]);

        $this->assertEquals($expected, $node);
    }

    // TODO: Consider adding test for 'parses kitchen sink'

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesKitchenSink()
    {
        $kitchenSink = mb_convert_encoding(file_get_contents(__DIR__ . '/kitchen-sink.graphql'), 'UTF-8');

        parse($kitchenSink);
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testAllowsNonKeywordsAnywhereANameIsAllowed()
    {
        $nonKeywords = [
            'on',
            'fragment',
            'query',
            'mutation',
            'subscription',
            'true',
            'false',
        ];

        foreach ($nonKeywords as $keyword) {
            $fragmentName = $keyword;

            // You can't define or reference a fragment named `on`.
            if ($keyword === 'on') {
                $fragmentName = 'a';
            }

            parse(new Source("
query $keyword {
  ... $fragmentName
  ... on $keyword { field }
}
fragment $fragmentName on Type {
  $keyword($keyword: $$keyword)
    @$keyword($keyword: $keyword)
}"));

            $this->addToAssertionCount(1);
        }
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesAnonMutationOperations()
    {
        parse(new Source('
        mutation {
            mutationField
        }
        '));
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesAnonSubscriptionOperations()
    {
        parse(new Source('
        subscription {
            subscriptionField
        }
        '));
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesNamedMutationOperations()
    {
        parse(new Source('
        mutation Foo {
            mutationField
        }
        '));
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesNamedSubscriptionOperations()
    {
        parse(new Source('
        subscription Foo {
            subscriptionField
        }
        '));
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testCreatesAST()
    {
        /** @var DocumentNode $actual */
        $actual = parse(new Source('{
  node(id: 4) {
    id,
    name
  }
}
'));

        $this->assertEquals([
            'kind'        => NodeKindEnum::DOCUMENT,
            'loc'         => ['start' => 0, 'end' => 41],
            'definitions' => [
                [
                    'kind'                => NodeKindEnum::OPERATION_DEFINITION,
                    'loc'                 => ['start' => 0, 'end' => 40],
                    'operation'           => 'query',
                    'name'                => null,
                    'variableDefinitions' => [],
                    'directives'          => [],
                    'selectionSet'        => [
                        'kind'       => NodeKindEnum::SELECTION_SET,
                        'loc'        => ['start' => 0, 'end' => 40],
                        'selections' => [
                            [
                                'kind'         => NodeKindEnum::FIELD,
                                'loc'          => ['start' => 4, 'end' => 38],
                                'alias'        => null,
                                'name'         => [
                                    'kind'  => NodeKindEnum::NAME,
                                    'loc'   => ['start' => 4, 'end' => 8],
                                    'value' => 'node',
                                ],
                                'arguments'    => [
                                    [
                                        'kind'  => NodeKindEnum::ARGUMENT,
                                        'name'  => [
                                            'kind'  => NodeKindEnum::NAME,
                                            'loc'   => ['start' => 9, 'end' => 11],
                                            'value' => 'id',
                                        ],
                                        'value' => [
                                            'kind'  => NodeKindEnum::INT,
                                            'loc'   => ['start' => 13, 'end' => 14],
                                            'value' => '4',
                                        ],
                                        'loc'   => ['start' => 9, 'end' => 14],
                                    ],
                                ],
                                'directives'   => [],
                                'selectionSet' => [
                                    'kind'       => NodeKindEnum::SELECTION_SET,
                                    'loc'        => ['start' => 16, 'end' => 38],
                                    'selections' => [
                                        [
                                            'kind'         => NodeKindEnum::FIELD,
                                            'loc'          => ['start' => 22, 'end' => 24],
                                            'alias'        => null,
                                            'name'         => [
                                                'kind'  => NodeKindEnum::NAME,
                                                'loc'   => ['start' => 22, 'end' => 24],
                                                'value' => 'id',
                                            ],
                                            'arguments'    => [],
                                            'directives'   => [],
                                            'selectionSet' => null,
                                        ],
                                        [
                                            'kind'         => NodeKindEnum::FIELD,
                                            'loc'          => ['start' => 30, 'end' => 34],
                                            'alias'        => null,
                                            'name'         => [
                                                'kind'  => NodeKindEnum::NAME,
                                                'loc'   => ['start' => 30, 'end' => 34],
                                                'value' => 'name',
                                            ],
                                            'arguments'    => [],
                                            'directives'   => [],
                                            'selectionSet' => null,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $actual->toArray());
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testCreatesAstFromNamelessQueryWithoutVariables()
    {
        /** @var DocumentNode $actual */
        $actual = parse(new Source('query {
  node {
    id
  }
}
'));

        $this->assertEquals([
            'kind'        => NodeKindEnum::DOCUMENT,
            'loc'         => ['start' => 0, 'end' => 30],
            'definitions' => [
                [
                    'kind'                => NodeKindEnum::OPERATION_DEFINITION,
                    'loc'                 => ['start' => 0, 'end' => 29],
                    'operation'           => 'query',
                    'name'                => null,
                    'variableDefinitions' => [],
                    'directives'          => [],
                    'selectionSet'        => [
                        'kind'       => NodeKindEnum::SELECTION_SET,
                        'loc'        => ['start' => 6, 'end' => 29],
                        'selections' => [
                            [
                                'kind'         => NodeKindEnum::FIELD,
                                'loc'          => ['start' => 10, 'end' => 27],
                                'alias'        => null,
                                'name'         => [
                                    'kind'  => NodeKindEnum::NAME,
                                    'loc'   => ['start' => 10, 'end' => 14],
                                    'value' => 'node',
                                ],
                                'arguments'    => [],
                                'directives'   => [],
                                'selectionSet' => [
                                    'kind'       => NodeKindEnum::SELECTION_SET,
                                    'loc'        => ['start' => 15, 'end' => 27],
                                    'selections' => [
                                        [
                                            'kind'         => NodeKindEnum::FIELD,
                                            'loc'          => ['start' => 21, 'end' => 23],
                                            'alias'        => null,
                                            'name'         => [
                                                'kind'  => NodeKindEnum::NAME,
                                                'loc'   => ['start' => 21, 'end' => 23],
                                                'value' => 'id',
                                            ],
                                            'arguments'    => [],
                                            'directives'   => [],
                                            'selectionSet' => null,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $actual->toArray());
    }

    // TODO: Consider adding test for 'allows parsing without source location information'

    // TODO: Consider adding test for 'Experimental: allows parsing fragment defined variables'

    // TODO: Consider adding test for 'contains location information that only stringifys start/end'

    // Skip 'contains references to source' (not provided by cpp parser)

    // Skip 'contains references to start and end tokens' (not provided by cpp parser)

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesNullValue()
    {
        /** @var NullValueNode $node */
        $node = parseValue('null');

        $this->assertEquals($node->toArray(), [
            'kind' => NodeKindEnum::NULL,
            'loc'  => ['start' => 0, 'end' => 4],
        ]);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesListValue()
    {
        /** @var NullValueNode $node */
        $node = parseValue('[123 "abc"]');

        $this->assertEquals($node->toArray(), [
            'kind'   => NodeKindEnum::LIST,
            'loc'    => ['start' => 0, 'end' => 11],
            'values' => [
                [
                    'kind'  => NodeKindEnum::INT,
                    'loc'   => ['start' => 1, 'end' => 4],
                    'value' => '123',
                ],
                [
                    'kind'  => NodeKindEnum::STRING,
                    'loc'   => ['start' => 5, 'end' => 10],
                    'block' => false,
                    'value' => 'abc',
                ],
            ],
        ]);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesBlockStrings()
    {
        /** @var NullValueNode $node */
        $node = parseValue('["""long""" "short"]');

        $this->assertEquals($node->toArray(), [
            'kind'   => NodeKindEnum::LIST,
            'loc'    => ['start' => 0, 'end' => 20],
            'values' => [
                [
                    'kind'  => NodeKindEnum::STRING,
                    'loc'   => ['start' => 1, 'end' => 11],
                    'value' => 'long',
                    'block' => true,

                ],
                [
                    'kind'  => NodeKindEnum::STRING,
                    'loc'   => ['start' => 12, 'end' => 19],
                    'value' => 'short',
                    'block' => false,
                ],
            ],
        ]);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesWellKnownTypes()
    {
        /** @var NamedTypeNode $node */
        $node = parseType('String');

        $this->assertEquals($node->toArray(), [
            'kind' => NodeKindEnum::NAMED_TYPE,
            'loc'  => ['start' => 0, 'end' => 6],
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'loc'   => ['start' => 0, 'end' => 6],
                'value' => 'String',
            ],
        ]);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesCustomTypes()
    {
        /** @var NamedTypeNode $node */
        $node = parseType('MyType');

        $this->assertEquals($node->toArray(), [
            'kind' => NodeKindEnum::NAMED_TYPE,
            'loc'  => ['start' => 0, 'end' => 6],
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'loc'   => ['start' => 0, 'end' => 6],
                'value' => 'MyType',
            ],
        ]);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesListTypes()
    {
        /** @var NamedTypeNode $node */
        $node = parseType('[MyType]');

        $this->assertEquals($node->toArray(), [
            'kind' => NodeKindEnum::LIST_TYPE,
            'loc'  => ['start' => 0, 'end' => 8],
            'type' => [
                'kind' => NodeKindEnum::NAMED_TYPE,
                'loc'  => ['start' => 1, 'end' => 7],
                'name' => [
                    'kind'  => NodeKindEnum::NAME,
                    'loc'   => ['start' => 1, 'end' => 7],
                    'value' => 'MyType',
                ],
            ],
        ]);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesNonNullTypes()
    {
        /** @var NamedTypeNode $node */
        $node = parseType('MyType!');

        $this->assertEquals($node->toArray(), [
            'kind' => NodeKindEnum::NON_NULL_TYPE,
            'loc'  => ['start' => 0, 'end' => 7],
            'type' => [
                'kind' => NodeKindEnum::NAMED_TYPE,
                'loc'  => ['start' => 0, 'end' => 6],
                'name' => [
                    'kind'  => NodeKindEnum::NAME,
                    'loc'   => ['start' => 0, 'end' => 6],
                    'value' => 'MyType',
                ],
            ],
        ]);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesNestedTypes()
    {
        /** @var NamedTypeNode $node */
        $node = parseType('[MyType!]');

        $this->assertEquals($node->toArray(), [
            'kind' => NodeKindEnum::LIST_TYPE,
            'loc'  => ['start' => 0, 'end' => 9],
            'type' => [
                'kind' => NodeKindEnum::NON_NULL_TYPE,
                'loc'  => ['start' => 1, 'end' => 8],
                'type' => [
                    'kind' => NodeKindEnum::NAMED_TYPE,
                    'loc'  => ['start' => 1, 'end' => 7],
                    'name' => [
                        'kind'  => NodeKindEnum::NAME,
                        'loc'   => ['start' => 1, 'end' => 7],
                        'value' => 'MyType',
                    ],
                ],
            ],
        ]);
    }
}
