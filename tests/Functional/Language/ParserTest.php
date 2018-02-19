<?php

namespace Digia\GraphQL\Test\Functional\Language\AST;

use Digia\GraphQL\Language\Builder\ArgumentBuilder;
use Digia\GraphQL\Language\Builder\DocumentBuilder;
use Digia\GraphQL\Language\Builder\FieldBuilder;
use Digia\GraphQL\Language\Builder\NameBuilder;
use Digia\GraphQL\Language\Builder\OperationDefinitionBuilder;
use Digia\GraphQL\Language\Builder\SelectionSetBuilder;
use Digia\GraphQL\Language\Parser;
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
     * @var Parser
     */
    protected $parser;

    public function setUp()
    {
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

        $this->parser = new Parser($readers);
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     */
    public function testParse()
    {
        $node = $this->parser->parse(new Source('query { name }'));

//        var_dump($node, 'node');die;
    }

    /**
     * @throws \Digia\GraphQL\Error\GraphQLError
     */
    public function testParsesVariableInlineValues()
    {
        $this->parser->parse(new Source('{ field(complex: { a: { b: [ $var ] } }) }'));
    }
}
