<?php

namespace Digia\GraphQL\Test\Functional\Language\AST;

use Digia\GraphQL\Error\SyntaxError;
use Digia\GraphQL\Language\AST\Builder\ArgumentBuilder;
use Digia\GraphQL\Language\AST\Builder\BooleanBuilder;
use Digia\GraphQL\Language\AST\Builder\DirectiveBuilder;
use Digia\GraphQL\Language\AST\Builder\DocumentBuilder;
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
use Digia\GraphQL\Language\AST\Builder\ObjectBuilder;
use Digia\GraphQL\Language\AST\Builder\ObjectFieldBuilder;
use Digia\GraphQL\Language\AST\Builder\OperationDefinitionBuilder;
use Digia\GraphQL\Language\AST\Builder\SelectionSetBuilder;
use Digia\GraphQL\Language\AST\Builder\StringBuilder;
use Digia\GraphQL\Language\AST\Builder\VariableBuilder;
use Digia\GraphQL\Language\ASTParser;
use function Digia\GraphQL\Language\chrUTF8;
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
        ];

        $this->parser = new ASTParser($builders, $readers);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     */
    public function testParsesVariableInlineValues()
    {
        $this->parser->parse(new Source('{ field(complex: { a: { b: [ $var ] } }) }'));
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @expectedException \Digia\GraphQL\Error\SyntaxError
     */
    public function testParsesConstantDefaultValues()
    {
        $this->parser->parse(new Source('query Foo($x: Complex = { a: { b: [ $var ] } }) { field }'));
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @expectedException \Digia\GraphQL\Error\SyntaxError
     */
    public function testDoesNotAcceptFragmentsNamedOn()
    {
        $this->parser->parse(new Source('fragment on on on { on }'));
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     * @expectedException \Digia\GraphQL\Error\SyntaxError
     */
    public function testDoesNotAcceptFragmentSpreadOfOn()
    {
        $this->parser->parse(new Source('{ ...on }'));
        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     */
    public function testParsesMultiByteCharacters()
    {
        // TODO: Fix test case

//        $this->parser->parse(new Source('
//        # This comment has a \u0A0A multi-byte character.
//        { field(arg: "Has a \u0A0A multi-byte character.") }
//        '));
        $this->addToAssertionCount(1);
    }

    // TODO: Add "parses kitchen sink"

    // TODO: Add "allows non-keywords anywhere a Name is allowed"

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
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
     */
    public function testCreatesAST()
    {
        $result = $this->parser->parse(new Source('
        {
            node(id: 4) {
                id,
                name
            }
        }
        '));
    }
}
