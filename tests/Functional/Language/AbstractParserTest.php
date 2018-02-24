<?php

namespace Digia\GraphQL\Test\Functional\Language;

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
use Digia\GraphQL\Language\AST\Builder\VariableDefinitionBuilder;
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
use Digia\GraphQL\Test\TestCase;

abstract class AbstractParserTest extends TestCase
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
            new VariableDefinitionBuilder(),
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
}
