<?php

namespace Digia\GraphQL\Test\Unit\Language;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Language\TokenKindEnum;
use Digia\GraphQL\Test\TestCase;

/**
 * Class LexerTest
 * @package Digia\GraphQL\Test\Unit\Language
 */
class LexerTest extends TestCase
{
    public function testDisallowsUncommonControlCharacters(): void
    {
        $this->assertSyntaxError("\u{0007}", 'Cannot contain the invalid character "\u0007"', 1, 1);
    }

    public function testBomCharacter(): void
    {
        $this->assertLexerTokenPropertiesEqual("\u{FEFF} foo", TokenKindEnum::NAME, 2, 5, 'foo');
    }

    public function testRecordsLineAndNumber(): void
    {
        $token = $this->getLexer("\n \r\n \r  foo\n")->advance();

        $this->assertEquals(TokenKindEnum::NAME, $token->getKind());
        $this->assertEquals(8, $token->getStart());
        $this->assertEquals(11, $token->getEnd());
        $this->assertEquals(4, $token->getLine());
        $this->assertEquals(3, $token->getColumn());
        $this->assertEquals('foo', $token->getValue());
    }

    public function testCanBeJsonSerialized(): void
    {
        $token = $this->getLexer('foo')->advance();

        $this->assertJsonStringEqualsJsonString(\json_encode([
            'kind'   => 'Name',
            'value'  => 'foo',
            'line'   => 1,
            'column' => 1,
        ]), $token->toJSON());
    }

    public function testSkipsWhitespaceAndComments(): void
    {
        $whitespaceString = <<<EOD

    foo
    
    
    
EOD;

        $commentedString = <<<EOD
#comment
foo#comment
EOD;

        $this->assertLexerTokenPropertiesEqual($whitespaceString, TokenKindEnum::NAME, 5, 8, 'foo');
        $this->assertLexerTokenPropertiesEqual($commentedString, TokenKindEnum::NAME, 9, 12, 'foo');
        $this->assertLexerTokenPropertiesEqual(',,,foo,,,', TokenKindEnum::NAME, 3, 6, 'foo');
    }

    public function testErrorsRespectWhitespace(): void
    {
        $whitespaceErrorString = <<<EOD


    ?

EOD;

        try {
            $this->getLexer($whitespaceErrorString)->advance();
        } catch (SyntaxErrorException $e) {
            $this->assertEquals([['line' => 3, 'column' => 5]], $e->getLocationsAsArray());
        }
    }

    public function testStrings(): void
    {
        $this->assertLexerTokenPropertiesEqual('"simple"', TokenKindEnum::STRING, 0, 8, 'simple');
        $this->assertLexerTokenPropertiesEqual('" white space "', TokenKindEnum::STRING, 0, 15, ' white space ');
        $this->assertLexerTokenPropertiesEqual('"quote \\""', TokenKindEnum::STRING, 0, 10, 'quote "');
        $this->assertLexerTokenPropertiesEqual('"escaped \\n\\r\\b\\t\\f"', TokenKindEnum::STRING, 0, 20,
            'escaped \n\r\b\t\f');
        $this->assertLexerTokenPropertiesEqual('"slashes \\\\ \\/"', TokenKindEnum::STRING, 0, 15,
            'slashes \\ /');
        $this->assertLexerTokenPropertiesEqual('"unicode \\u1234\\u5678\\u90AB\\uCDEF"', TokenKindEnum::STRING, 0, 34,
            'unicode \u1234\u5678\u90AB\uCDEF');

        $this->assertSyntaxError('"', 'Unterminated string.', 1, 1);
    }

    public function testBlockStrings(): void
    {
        $this->assertLexerTokenPropertiesEqual('"""simple"""', TokenKindEnum::BLOCK_STRING, 0, 12, 'simple');
        $this->assertLexerTokenPropertiesEqual('""" white space """', TokenKindEnum::BLOCK_STRING, 0, 19,
            ' white space ');
        $this->assertLexerTokenPropertiesEqual('"""contains " quote"""', TokenKindEnum::BLOCK_STRING, 0, 22,
            'contains " quote');
        $this->assertLexerTokenPropertiesEqual('"""contains \\""" triplequote"""', TokenKindEnum::BLOCK_STRING, 0, 31,
            'contains """ triplequote');
        $this->assertLexerTokenPropertiesEqual('"""' . "multi\nline" . '""""', TokenKindEnum::BLOCK_STRING, 0, 16,
            "multi\nline");
        $this->assertLexerTokenPropertiesEqual('"""' . "multi\rline\r\nnormalized" . '""""',
            TokenKindEnum::BLOCK_STRING,
            0, 28,
            "multi\nline\nnormalized");
        $this->assertLexerTokenPropertiesEqual('"""' . "unescaped \\n\\r\\b\\t\\f\\u1234" . '""""',
            TokenKindEnum::BLOCK_STRING,
            0, 32,
            'unescaped \\n\\r\\b\\t\\f\\u1234');
        $this->assertLexerTokenPropertiesEqual('"""' . "slashes \\\\ \\/" . '""""',
            TokenKindEnum::BLOCK_STRING,
            0, 19,
            "slashes \\\\ \\/");

        $multiline = <<<EOD
"""

spans
multiple
lines
    
"""
EOD;

        $this->assertLexerTokenPropertiesEqual($multiline,
            TokenKindEnum::BLOCK_STRING,
            0, 34,
            "spans\nmultiple\nlines");
    }

    /**
     * @param string $source
     * @param string $expectedExceptionMessage
     * @param int    $line
     * @param int    $column
     */
    private function assertSyntaxError(string $source, string $expectedExceptionMessage, int $line, int $column): void
    {
        try {
            $this->getLexer($source)->advance();

            $this->fail('Expected an exception to be thrown');
        } catch (SyntaxErrorException $e) {
            $this->assertEquals('Syntax Error: ' . $expectedExceptionMessage, $e->getMessage());
            $this->assertEquals([['line' => $line, 'column' => $column]], $e->getLocationsAsArray());
        }
    }

    /**
     * @param string $source
     * @param string $kind
     * @param int    $start
     * @param int    $end
     * @param mixed  $value
     */
    private function assertLexerTokenPropertiesEqual(string $source, string $kind, int $start, int $end, $value): void
    {
        $token = $this->getLexer($source)->advance();

        $this->assertEquals($kind, $token->getKind());
        $this->assertEquals($start, $token->getStart());
        $this->assertEquals($end, $token->getEnd());
        $this->assertEquals($value, $token->getValue());
    }

    /**
     * @param string $source
     * @return LexerInterface
     */
    private function getLexer(string $source): LexerInterface
    {
        $lexer = GraphQL::make(LexerInterface::class);
        $lexer->setSource(new Source($source));

        return $lexer;
    }
}
