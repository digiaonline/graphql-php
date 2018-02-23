<?php

namespace Digia\GraphQL\Test\Functional\Language\AST;

use Digia\GraphQL\Error\SyntaxError;
use Digia\GraphQL\Language\AST\Builder\ArgumentBuilder;
use Digia\GraphQL\Language\AST\Builder\BooleanBuilder;
use Digia\GraphQL\Language\AST\Builder\DirectiveBuilder;
use Digia\GraphQL\Language\AST\Builder\DocumentBuilder;
use Digia\GraphQL\Language\AST\Builder\EnumBuilder;
use Digia\GraphQL\Language\AST\Builder\FieldBuilder;
use Digia\GraphQL\Language\AST\Builder\FloatBuilder;
use Digia\GraphQL\Language\AST\Builder\FragmentDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\FragmentSpreadBuilder;
use Digia\GraphQL\Language\AST\Builder\InlineFragmentBuilder;
use Digia\GraphQL\Language\AST\Builder\IntBuilder;
use Digia\GraphQL\Language\AST\Builder\ListBuilder;
use Digia\GraphQL\Language\AST\Builder\ListTypeBuilder;
use Digia\GraphQL\Language\AST\Builder\NameBuilder;
use Digia\GraphQL\Language\AST\Builder\NamedTypeBuilder;
use Digia\GraphQL\Language\AST\Builder\NonNullTypeBuilder;
use Digia\GraphQL\Language\AST\Builder\NullBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectFieldBuilder;
use Digia\GraphQL\Language\AST\Builder\OperationDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\SelectionSetBuilder;
use Digia\GraphQL\Language\AST\Builder\StringBuilder;
use Digia\GraphQL\Language\AST\Builder\VariableBuilder;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;
use Digia\GraphQL\Language\AST\Node\NullValueNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\ASTParser;
use Digia\GraphQL\Language\Contract\ParserInterface;
use Digia\GraphQL\Language\Reader\AmpReader;
use Digia\GraphQL\Language\Reader\AtReader;
use Digia\GraphQL\Language\Reader\BangReader;
use Digia\GraphQL\Language\Reader\BlockStringReader;
use Digia\GraphQL\Language\Reader\BraceReader;
use Digia\GraphQL\Language\Reader\BracketReader;
use Digia\GraphQL\Language\Reader\ColonReader;
use Digia\GraphQL\Language\Reader\CommentReader;
use Digia\GraphQL\Language\Reader\DollarReader;
use Digia\GraphQL\Language\Reader\EqualsReader;
use Digia\GraphQL\Language\Reader\NameReader;
use Digia\GraphQL\Language\Reader\NumberReader;
use Digia\GraphQL\Language\Reader\ParenthesisReader;
use Digia\GraphQL\Language\Reader\PipeReader;
use Digia\GraphQL\Language\Reader\SpreadReader;
use Digia\GraphQL\Language\Reader\StringReader;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Test\TestCase;

class ParserTest extends TestCase
{

    /**
     * @var ParserInterface
     */
    protected $parser;

    public function setUp()
    {
        $builders = [
            new ArgumentBuilder(),
            new BooleanBuilder(),
            new DirectiveBuilder(),
            new DocumentBuilder(),
            new EnumBuilder(),
            new FieldBuilder(),
            new FloatBuilder(),
            new FragmentDefinitionBuilder(),
            new FragmentSpreadBuilder(),
            new InlineFragmentBuilder(),
            new IntBuilder(),
            new ListBuilder(),
            new ListTypeBuilder(),
            new NameBuilder(),
            new NamedTypeBuilder(),
            new NonNullTypeBuilder(),
            new NullBuilder(),
            new ObjectBuilder(),
            new ObjectFieldBuilder(),
            new OperationDefinitionBuilder(),
            new SelectionSetBuilder(),
            new StringBuilder(),
            new VariableBuilder(),
        ];

        $readers = [
            new AmpReader(),
            new AtReader(),
            new BangReader(),
            new BlockStringReader(),
            new BraceReader(),
            new BracketReader(),
            new ColonReader(),
            new CommentReader(),
            new DollarReader(),
            new EqualsReader(),
            new NameReader(),
            new NumberReader(),
            new ParenthesisReader(),
            new PipeReader(),
            new SpreadReader(),
            new StringReader(),
        ];

        $this->parser = new ASTParser($builders, $readers);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParseProvidesUsefulErrors()
    {
        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Expected Name, found <EOF>');
        $this->parser->parse(new Source('{'));

        $this->expectException(SyntaxError::class);
        $this->expectExceptionMessage('Expected {, found <EOF>');
        $this->parser->parse(new Source('query', 'MyQuery.graphql'));
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesVariableInlineValues()
    {
        $this->parser->parse(new Source('{ field(complex: { a: { b: [ $var ] } }) }'));
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
        $this->parser->parse(new Source('query Foo($x: Complex = { a: { b: [ $var ] } }) { field }'));
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
        $this->parser->parse(new Source('fragment on on on { on }'));
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
        $this->parser->parse(new Source('{ ...on }'));
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @throws \Exception
     */
    public function testParsesMultiByteCharacters()
    {
        /** @var DocumentNode $node */
        $node = $this->parser->parse(new Source('
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

    // TODO: Consider adding test for 'parses kitchen sink'

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

            $this->parser->parse(new Source("
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
        $this->parser->parse(new Source('
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
        $this->parser->parse(new Source('
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
        $this->parser->parse(new Source('
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
        $this->parser->parse(new Source('
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
        $actual = $this->parser->parse(new Source('{
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
        $actual = $this->parser->parse(new Source('query {
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
        $node = $this->parser->parseValue(new Source('null'));

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
        $node = $this->parser->parseValue(new Source('[123 "abc"]'));

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
        $node = $this->parser->parseValue(new Source('["""long""" "short"]'));

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
        $node = $this->parser->parseType(new Source('String'));

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
        $node = $this->parser->parseType(new Source('MyType'));

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
        $node = $this->parser->parseType(new Source('[MyType]'));

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
        $node = $this->parser->parseType(new Source('MyType!'));

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
        $node = $this->parser->parseType(new Source('[MyType!]'));

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
