<?php

namespace Digia\GraphQL\Test\Unit\Language;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\Lexer;
use Digia\GraphQL\Language\LexerInterface;
use Digia\GraphQL\Language\Source;
use Digia\GraphQL\Language\SourceLocation;
use Digia\GraphQL\Language\Token;
use Digia\GraphQL\Language\TokenKindEnum;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Language\dedent;

/**
 * Class LexerTest
 * @package Digia\GraphQL\Test\Unit\Language
 */
class LexerTest extends TestCase
{
    // disallows uncommon control characters

    public function testDisallowsUncommonControlCharacters(): void
    {
        $this->assertSyntaxError("\u{0007}", 'Cannot contain the invalid character "\u0007".', [1, 1]);
    }

    // accepts BOM header

    public function testAcceptsBomCharacter(): void
    {
        $this->assertLexerTokenPropertiesEqual("\u{FEFF} foo", TokenKindEnum::NAME, [2, 5], 'foo');
    }

    // records line and column

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

    // can be JSON.stringified or util.inspected

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

    // skips whitespace and comments

    public function testSkipsWhitespaceAndComments(): void
    {
        $whitespaceString = <<<EOD

    foo
    
    
    
EOD;

        $commentedString = <<<EOD
#comment
foo#comment
EOD;

        $this->assertLexerTokenPropertiesEqual($whitespaceString, TokenKindEnum::NAME, [5, 8], 'foo');

        $this->assertLexerTokenPropertiesEqual($commentedString, TokenKindEnum::NAME, [9, 12], 'foo');

        $this->assertLexerTokenPropertiesEqual(',,,foo,,,', TokenKindEnum::NAME, [3, 6], 'foo');
    }

    // errors respect whitespace

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

    // updates line numbers in error for file context

    public function testUpdatesLineNumbersInErrorForFileContext()
    {
        $caughtError = null;

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $source = new Source("\n\n     ?\n\n", 'foo.php', new SourceLocation(11, 12));
            $this->getLexer($source)->advance();
        } catch (SyntaxErrorException $e) {
            $caughtError = $e;
        }

        $this->assertEquals(
            "Syntax Error: Cannot parse the unexpected character \"?\".\n" .
            "\n" .
            "foo.php (13:6)\n" .
            "12: \n" .
            "13:      ?\n" .
            "         ^\n" .
            "14: \n",
            (string)$caughtError
        );
    }

    // updates column numbers in error for file context

    public function testUpdatesColumnNumbersInErrorForFileContext()
    {
        $caughtError = null;

        try {
            /** @noinspection PhpUnhandledExceptionInspection */
            $source = new Source('?', 'foo.php', new SourceLocation(1, 5));
            $this->getLexer($source)->advance();
        } catch (SyntaxErrorException $e) {
            $caughtError = $e;
        }

        $this->assertEquals(
            "Syntax Error: Cannot parse the unexpected character \"?\".\n" .
            "\n" .
            "foo.php (1:5)\n" .
            "1:     ?\n" .
            "       ^\n",
            (string)$caughtError
        );
    }

    // lexes strings

    public function testLexesStrings(): void
    {
        $this->assertLexerTokenPropertiesEqual('"simple"', TokenKindEnum::STRING, [0, 8], 'simple');

        $this->assertLexerTokenPropertiesEqual('" white space "', TokenKindEnum::STRING, [0, 15], ' white space ');

        $this->assertLexerTokenPropertiesEqual('"quote \\""', TokenKindEnum::STRING, [0, 10], 'quote "');

        $this->assertLexerTokenPropertiesEqual(
            '"escaped \\n\\r\\b\\t\\f"',
            TokenKindEnum::STRING,
            [0, 20],
            'escaped \n\r\b\t\f'
        );

        $this->assertLexerTokenPropertiesEqual('"slashes \\\\ \\/"', TokenKindEnum::STRING, [0, 15], 'slashes \\ /');

        $this->assertLexerTokenPropertiesEqual(
            '"unicode \\u1234\\u5678\\u90AB\\uCDEF"',
            TokenKindEnum::STRING,
            [0, 34],
            'unicode \u1234\u5678\u90AB\uCDEF'
        );
    }

    // lex reports useful string errors

    public function testLexReportsUsefulStringErrors()
    {
        $this->assertSyntaxError('"', 'Unterminated string.', [1, 2]);

        $this->assertSyntaxError('"no end quote', 'Unterminated string.', [1, 14]);

        $this->assertSyntaxError(
            "'single quotes'",
            'Unexpected single quote character (\'), did you mean to use a double quote (")?',
            [1, 1]
        );

        $this->assertSyntaxError(
            "\"contains unescaped \u{0007} control char\"",
            'Invalid character within String: "\\u0007".',
            [1, 21]
        );

        // TODO: Fix the following EOF-related test

//        $this->assertSyntaxError(
//            "\"null-byte is not \u{0000} end of file\"",
//            'Invalid character within String: "\\u0000".',
//            [1, 19]
//        );

        $this->assertSyntaxError("\"multi\nline\"", 'Unterminated string.', [1, 7]);

        $this->assertSyntaxError("\"multi\rline\"", 'Unterminated string.', [1, 7]);

        $this->assertSyntaxError(
            "\"bad \\z esc\"",
            'Invalid character escape sequence: \\z.',
            [1, 7]
        );

        $this->assertSyntaxError(
            "\"bad \\x esc\"",
            'Invalid character escape sequence: \\x.',
            [1, 7]
        );

        $this->assertSyntaxError(
            "\"bad \\u1 esc\"",
            'Invalid character escape sequence: \\u1 es.',
            [1, 7]
        );

        $this->assertSyntaxError(
            "\"bad \\u0XX1 esc\"",
            'Invalid character escape sequence: \\u0XX1.',
            [1, 7]
        );

        $this->assertSyntaxError(
            "\"bad \\uXXXX esc\"",
            'Invalid character escape sequence: \\uXXXX.',
            [1, 7]
        );

        $this->assertSyntaxError(
            "\"bad \\uFXXX esc\"",
            'Invalid character escape sequence: \\uFXXX.',
            [1, 7]
        );

        $this->assertSyntaxError(
            "\"bad \\uXXXF esc\"",
            'Invalid character escape sequence: \\uXXXF.',
            [1, 7]
        );
    }

    // lexes block strings

    public function testLexesBlockStrings(): void
    {
        $this->assertLexerTokenPropertiesEqual(
            '"""simple"""',
            TokenKindEnum::BLOCK_STRING,
            [0, 12],
            'simple'
        );

        $this->assertLexerTokenPropertiesEqual(
            '""" white space """',
            TokenKindEnum::BLOCK_STRING,
            [0, 19],
            ' white space '
        );
        $this->assertLexerTokenPropertiesEqual(
            '"""contains " quote"""',
            TokenKindEnum::BLOCK_STRING,
            [0, 22],
            'contains " quote'
        );

        $this->assertLexerTokenPropertiesEqual(
            '"""contains \\""" triplequote"""',
            TokenKindEnum::BLOCK_STRING,
            [0, 31],
            'contains """ triplequote'
        );

        $this->assertLexerTokenPropertiesEqual(
            '"""' . "multi\nline" . '""""',
            TokenKindEnum::BLOCK_STRING,
            [0, 16],
            "multi\nline"
        );

        $this->assertLexerTokenPropertiesEqual(
            '"""' . "multi\rline\r\nnormalized" . '""""',
            TokenKindEnum::BLOCK_STRING,
            [0, 28],
            "multi\nline\nnormalized"
        );

        $this->assertLexerTokenPropertiesEqual(
            '"""' . "unescaped \\n\\r\\b\\t\\f\\u1234" . '""""',
            TokenKindEnum::BLOCK_STRING,
            [0, 32],
            'unescaped \\n\\r\\b\\t\\f\\u1234'
        );

        $this->assertLexerTokenPropertiesEqual(
            '"""' . "slashes \\\\ \\/" . '""""',
            TokenKindEnum::BLOCK_STRING,
            [0, 19],
            "slashes \\\\ \\/"
        );

        $this->assertLexerTokenPropertiesEqual(
            dedent('
            """
            
            spans
              multiple
                lines
                
            """
            '),
            TokenKindEnum::BLOCK_STRING,
            [0, 40],
            "spans\n  multiple\n    lines"
        );
    }

    // lex reports useful block string errors

    public function testLexReportsUsefulBlockStringErrors()
    {
        $this->assertSyntaxError('"""', 'Unterminated string.', [1, 4]);

        $this->assertSyntaxError('"""no end quote', 'Unterminated string.', [1, 16]);

        $this->assertSyntaxError(
            "\"\"\"contains unescaped \u{0007} control char\"\"\"",
            'Invalid character within String: "\\u0007".',
            [1, 23]
        );

        // TODO: Fix the following EOF-related test

//        $this->assertSyntaxError(
//            "\"\"\"null-byte is not \u{0000} end of file\"\"\"",
//            'Invalid character within String: "\\u0000".',
//            [1, 21]
//        );
    }

    // lexes numbers

    public function testLexesNumbers()
    {
        $this->assertLexerTokenPropertiesEqual('4', TokenKindEnum::INT, [0, 1], '4');

        $this->assertLexerTokenPropertiesEqual('4.123', TokenKindEnum::FLOAT, [0, 5], '4.123');

        $this->assertLexerTokenPropertiesEqual('-4', TokenKindEnum::INT, [0, 2], '-4');

        $this->assertLexerTokenPropertiesEqual('9', TokenKindEnum::INT, [0, 1], '9');

        $this->assertLexerTokenPropertiesEqual('0', TokenKindEnum::INT, [0, 1], '0');

        $this->assertLexerTokenPropertiesEqual('-4.123', TokenKindEnum::FLOAT, [0, 6], '-4.123');

        $this->assertLexerTokenPropertiesEqual('0.123', TokenKindEnum::FLOAT, [0, 5], '0.123');

        $this->assertLexerTokenPropertiesEqual('123e4', TokenKindEnum::FLOAT, [0, 5], '123e4');

        $this->assertLexerTokenPropertiesEqual('123E4', TokenKindEnum::FLOAT, [0, 5], '123E4');

        $this->assertLexerTokenPropertiesEqual('123e-4', TokenKindEnum::FLOAT, [0, 6], '123e-4');

        $this->assertLexerTokenPropertiesEqual('123E-4', TokenKindEnum::FLOAT, [0, 6], '123E-4');

        $this->assertLexerTokenPropertiesEqual('123e+4', TokenKindEnum::FLOAT, [0, 6], '123e+4');

        $this->assertLexerTokenPropertiesEqual('-1.123e4', TokenKindEnum::FLOAT, [0, 8], '-1.123e4');

        $this->assertLexerTokenPropertiesEqual('-1.123E4', TokenKindEnum::FLOAT, [0, 8], '-1.123E4');

        $this->assertLexerTokenPropertiesEqual('-1.123e+4', TokenKindEnum::FLOAT, [0, 9], '-1.123e+4');

        $this->assertLexerTokenPropertiesEqual('-1.123e4567', TokenKindEnum::FLOAT, [0, 11], '-1.123e4567');
    }

    // lex reports useful number errors

    public function testLexReportsUsefulNumberErrors()
    {
        $this->assertSyntaxError('00', 'Invalid number, unexpected digit after 0: "0".', [1, 2]);

        $this->assertSyntaxError('+1', 'Cannot parse the unexpected character "+".', [1, 1]);

        $this->assertSyntaxError('1.', 'Invalid number, expected digit but got: <EOF>.', [1, 3]);

        $this->assertSyntaxError('1.e1', 'Invalid number, expected digit but got: "e".', [1, 3]);

        $this->assertSyntaxError('.123', 'Cannot parse the unexpected character ".".', [1, 1]);

        $this->assertSyntaxError('1.A', 'Invalid number, expected digit but got: "A".', [1, 3]);

        $this->assertSyntaxError('-A', 'Invalid number, expected digit but got: "A".', [1, 2]);

        $this->assertSyntaxError('1.0e', 'Invalid number, expected digit but got: <EOF>.', [1, 5]);

        $this->assertSyntaxError('1.0eA', 'Invalid number, expected digit but got: "A".', [1, 5]);
    }

    // lexes punctuation

    public function testLexesPunctuation()
    {
        $this->assertLexerTokenPropertiesEqual('!', TokenKindEnum::BANG, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual('$', TokenKindEnum::DOLLAR, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual('(', TokenKindEnum::PAREN_L, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual(')', TokenKindEnum::PAREN_R, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual('...', TokenKindEnum::SPREAD, [0, 3], null);
        $this->assertLexerTokenPropertiesEqual(':', TokenKindEnum::COLON, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual('=', TokenKindEnum::EQUALS, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual('@', TokenKindEnum::AT, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual('[', TokenKindEnum::BRACKET_L, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual(']', TokenKindEnum::BRACKET_R, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual('{', TokenKindEnum::BRACE_L, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual('|', TokenKindEnum::PIPE, [0, 1], null);
        $this->assertLexerTokenPropertiesEqual('}', TokenKindEnum::BRACE_R, [0, 1], null);
    }

    // lex reports useful unknown character error

    public function testLexReportsUsefulUnknownCharacterError()
    {
        $this->assertSyntaxError('..', 'Cannot parse the unexpected character ".".', [1, 1]);

        $this->assertSyntaxError('?', 'Cannot parse the unexpected character "?".', [1, 1]);

        $this->assertSyntaxError("\u{203B}", 'Cannot parse the unexpected character "\\u203b".', [1, 1]);

        $this->assertSyntaxError("\u{200b}", 'Cannot parse the unexpected character "\\u200b".', [1, 1]);
    }

    // lex reports useful information for dashes in names

    public function testLexReportsUsefulInformationForDashesInNames()
    {
        $lexer      = $this->getLexer('a-b');
        $firstToken = $lexer->advance();

        $this->assertTokenPropertiesEqual($firstToken, TokenKindEnum::NAME, [0, 1], 'a');

        $caughtError = null;

        try {
            $lexer->advance();
        } catch (SyntaxErrorException $e) {
            $caughtError = $e;
        }

        $this->assertEquals('Syntax Error: Invalid number, expected digit but got: "b".', $caughtError->getMessage());
        $this->assertEquals([['line' => 1, 'column' => 3]], $caughtError->getLocationsAsArray());
    }

    // produces double linked list of tokens, including comments

    public function testProducesDoubleLinkedListOfTokensIncludingComments()
    {
        $lexer = $this->getLexer(dedent('
        {
          #comment
          field
        }
        '));

        $startToken = $lexer->getToken();
        $endToken   = null;

        do {
            $endToken = $lexer->advance();
            // Lexer advances over ignored comment tokens to make writing parsers
            // easier, but will include them in the linked list result.
            $this->assertNotEquals(TokenKindEnum::COMMENT, $endToken->getKind());
        } while ($endToken->getKind() !== TokenKindEnum::EOF);

        $this->assertNull($startToken->getPrev());
        $this->assertNull($endToken->getNext());

        $tokens = [];

        for ($token = $startToken; null !== $token; $token = $token->getNext()) {
            if (!empty($tokens)) {
                // Tokens are double-linked, prev should point to last seen token.
                $this->assertEquals($tokens[\count($tokens) - 1], $token->getPrev());
            }

            $tokens[] = $token;
        }

        $this->assertEquals([
            '<SOF>',
            '{',
            'Comment',
            'Name',
            '}',
            '<EOF>',
        ], \array_map(function (Token $token) {
            return $token->getKind();
        }, $tokens));
    }

    /**
     * @param string $source
     * @param string $expectedExceptionMessage
     * @param int    $line
     * @param int    $column
     */
    private function assertSyntaxError(string $source, string $expectedExceptionMessage, array $position): void
    {
        try {
            $this->getLexer($source)->advance();

            $this->fail('Expected an exception to be thrown');
        } catch (SyntaxErrorException $e) {
            $this->assertEquals('Syntax Error: ' . $expectedExceptionMessage, $e->getMessage());
            $this->assertEquals([['line' => $position[0], 'column' => $position[1]]], $e->getLocationsAsArray());
        }
    }

    /**
     * @param string $source
     * @param string $kind
     * @param array  $location
     * @param mixed  $value
     */
    private function assertLexerTokenPropertiesEqual(string $source, string $kind, array $location, $value): void
    {
        $token = $this->getLexer($source)->advance();

        $this->assertTokenPropertiesEqual($token, $kind, $location, $value);
    }

    /**
     * @param Token  $token
     * @param string $kind
     * @param array  $location
     * @param mixed  $value
     */
    private function assertTokenPropertiesEqual(Token $token, string $kind, array $location, $value): void
    {
        $this->assertEquals($kind, $token->getKind());
        $this->assertEquals($location[0], $token->getStart());
        $this->assertEquals($location[1], $token->getEnd());
        $this->assertEquals($value, $token->getValue());
    }

    /**
     * @param Source|string $source
     * @return LexerInterface
     */
    private function getLexer($source, array $options = []): LexerInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new Lexer($source instanceof Source ? $source : new Source($source), $options);
    }
}
