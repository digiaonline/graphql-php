<?php

namespace Digia\GraphQL\Test\Functional\Language\AST;

use Digia\GraphQL\Language\AST\Parser;
use Digia\GraphQL\Test\TestCase;

class ParserTest extends TestCase
{

    /**
     * @var Parser
     */
    protected $parser;

    public function setUp()
    {
        $this->parser = new Parser();
    }

    public function testParse()
    {
        $this->parser->parse('query { name }');
    }
}
