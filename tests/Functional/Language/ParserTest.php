<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Language\SyntaxErrorException;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\NameNode;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Language\ParserInterface;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\parseType;
use function Digia\GraphQL\parseValue;
use function Digia\GraphQL\Test\readFileContents;

class ParserTest extends TestCase
{

    /**
     * @throws InvariantException
     */
    public function testParsePartial()
    {
        /** @var ParserInterface $parser */
        $parser = GraphQL::make(ParserInterface::class);

        /** @var NameNode $nameNode */
        $nameNode = $parser->parseName(new Source('foo'));

        $this->assertInstanceOf(NameNode::class, $nameNode);
        $this->assertEquals('foo', $nameNode->getValue());

        /** @var OperationDefinitionNode $operationDefinition */
        $operationDefinition = $parser->parseOperationDefinition(new Source('query FooQuery { foo }'));

        $this->assertInstanceOf(OperationDefinitionNode::class, $operationDefinition);
        $this->assertEquals('query', $operationDefinition->getOperation());
        $this->assertEquals('FooQuery', $operationDefinition->getNameValue());

        /** @var VariableDefinitionNode $variableDefinitionNode */
        $variableDefinitionNode = $parser->parseVariableDefinition(new Source('$foo: String = "bar"'));

        $this->assertInstanceOf(VariableDefinitionNode::class, $variableDefinitionNode);
        $this->assertEquals('foo', $variableDefinitionNode->getVariable()->getNameValue());
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals('String', $variableDefinitionNode->getType()->getNameValue());
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals('bar', $variableDefinitionNode->getDefaultValue()->getValue());

        /** @var VariableNode $variableNode */
        $variableNode = $parser->parseVariable(new Source('$foo'));

        $this->assertInstanceOf(VariableNode::class, $variableNode);
        $this->assertEquals('foo', $variableNode->getNameValue());

        /** @var ArgumentNode $argumentNode */
        $argumentNode = $parser->parseArgument(new Source('foo: String'));

        $this->assertInstanceOf(ArgumentNode::class, $argumentNode);
        $this->assertEquals('foo', $argumentNode->getNameValue());

        /** @var DirectiveNode $directiveNode */
        $directiveNode = $parser->parseDirective(new Source('@foo(bar: String, baz: Int)'));
        $directiveArgs = $directiveNode->getArguments();

        $this->assertInstanceOf(DirectiveNode::class, $directiveNode);
        $this->assertEquals('foo', $directiveNode->getNameValue());
        $this->assertEquals('bar', $directiveArgs[0]->getNameValue());
        $this->assertEquals('baz', $directiveArgs[1]->getNameValue());
    }

    public function testParseProvidesUsefulErrors()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Expected Name, found <EOF>');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('{');

        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Expected {, found <EOF>');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('query', 'MyQuery.graphql');
    }

    public function testParsesVariableInlineValues()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('{ field(complex: { a: { b: [ $var ] } }) }');
        $this->addToAssertionCount(1);
    }

    public function testParsesConstantDefaultValues()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Unexpected $');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('query Foo($x: Complex = { a: { b: [ $var ] } }) { field }');
        $this->addToAssertionCount(1);
    }

    public function testDoesNotAcceptFragmentsNamedOn()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Unexpected Name "on"');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('fragment on on on { on }');
        $this->addToAssertionCount(1);
    }

    public function testDoesNotAcceptFragmentSpreadOfOn()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Expected Name, found }');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('{ ...on }');
        $this->addToAssertionCount(1);
    }

    public function testParsesMultiByteCharacters()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse(dedent('
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
                                            'kind'  => NodeKindEnum::STRING,
                                            'value' => 'Has a \u0A0A multi-byte character.',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ], $node->toAST());
    }

    public function testParsesKitchenSink()
    {
        $kitchenSink = readFileContents(__DIR__ . '/kitchen-sink.graphql');

        /** @noinspection PhpUnhandledExceptionInspection */
        parse($kitchenSink);
        $this->addToAssertionCount(1);
    }

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

            /** @noinspection PhpUnhandledExceptionInspection */
            parse(dedent("
            query $keyword {
              ... $fragmentName
              ... on $keyword { field }
            }
            fragment $fragmentName on Type {
              $keyword($keyword: $$keyword)
                @$keyword($keyword: $keyword)
            }
            "));

            $this->addToAssertionCount(1);
        }
    }

    public function testParsesAnonMutationOperations()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        parse(dedent('
        mutation {
            mutationField
        }
        '));
        $this->addToAssertionCount(1);
    }

    public function testParsesAnonSubscriptionOperations()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        parse(dedent('
        subscription {
            subscriptionField
        }
        '));
        $this->addToAssertionCount(1);
    }

    public function testParsesNamedMutationOperations()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        parse(dedent('
        mutation Foo {
            mutationField
        }
        '));
        $this->addToAssertionCount(1);
    }

    public function testParsesNamedSubscriptionOperations()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        parse(dedent('
        subscription Foo {
            subscriptionField
        }
        '));
        $this->addToAssertionCount(1);
    }

    public function testCreatesAST()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $actual = parse(dedent('
        {
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
        ], $actual->toAST());
    }

    public function testCreatesAstFromNamelessQueryWithoutVariables()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $actual = parse(dedent('
        query {
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
        ], $actual->toAST());
    }

    // TODO: Consider adding test for 'allows parsing without source location information'

    // TODO: Consider adding test for 'Experimental: allows parsing fragment defined variables'

    // TODO: Consider adding test for 'contains location information that only stringifys start/end'

    // Skip 'contains references to source' (not provided by cpp parser)

    // Skip 'contains references to start and end tokens' (not provided by cpp parser)

    public function testParsesNullValue()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parseValue('null');

        $this->assertEquals($node->toAST(), [
            'kind' => NodeKindEnum::NULL,
            'loc'  => ['start' => 0, 'end' => 4],
        ]);
    }

    public function testParsesListValue()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parseValue('[123 "abc"]');

        $this->assertEquals($node->toAST(), [
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

    public function testParsesBlockStrings()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parseValue('["""long""" "short"]');

        $this->assertEquals($node->toAST(), [
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

    public function testParsesWellKnownTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parseType('String');

        $this->assertEquals($node->toAST(), [
            'kind' => NodeKindEnum::NAMED_TYPE,
            'loc'  => ['start' => 0, 'end' => 6],
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'loc'   => ['start' => 0, 'end' => 6],
                'value' => 'String',
            ],
        ]);
    }

    public function testParsesCustomTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parseType('MyType');

        $this->assertEquals($node->toAST(), [
            'kind' => NodeKindEnum::NAMED_TYPE,
            'loc'  => ['start' => 0, 'end' => 6],
            'name' => [
                'kind'  => NodeKindEnum::NAME,
                'loc'   => ['start' => 0, 'end' => 6],
                'value' => 'MyType',
            ],
        ]);
    }

    public function testParsesListTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parseType('[MyType]');

        $this->assertEquals($node->toAST(), [
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

    public function testParsesNonNullTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parseType('MyType!');

        $this->assertEquals($node->toAST(), [
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

    public function testParsesNestedTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parseType('[MyType!]');

        $this->assertEquals($node->toAST(), [
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

    /**
     * The purpose of this test case is that it should *not* crash with "Syntax Error: Cannot contain the invalid character <EOF>"
     * @throws SyntaxErrorException
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testParsesGitHubIssue253(): void
    {
        $document = parse(dedent('{
  songs(first: 5, search: {name: "Vårt land"}) {
    edges {
      node {
        name
      }
    }
  }
}
        '));

        $this->addToAssertionCount(1);
    }
}
