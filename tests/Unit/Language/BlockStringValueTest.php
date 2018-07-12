<?php

namespace Digia\GraphQL\Test\Unit\Language;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Language\blockStringValue;

/**
 * Class BlockStringValueTest
 * @package Digia\GraphQL\Test\Unit\Language
 */
class BlockStringValueTest extends TestCase
{

    public function testRemovesUniformIndentation(): void
    {
        $rawStringLines = [
            '',
            '    Hello',
            '      World!',
            '',
            '    Yours,',
            '      GraphQL.'
        ];

        $expectedBlockStringLines = [
            'Hello',
            '  World!',
            '',
            'Yours,',
            '  GraphQL.'
        ];

        $this->assertBlockStringEquals($expectedBlockStringLines, $rawStringLines);
    }

    public function testRemovesEmptyLeadingAndTrailingLines(): void
    {
        $rawStringLines = [
            '',
            '',
            '    Hello',
            '      World!',
            '',
            '    Yours,',
            '      GraphQL.',
            '',
            '',
        ];

        $expectedBlockStringLines = [
            'Hello',
            '  World!',
            '',
            'Yours,',
            '  GraphQL.'
        ];

        $this->assertBlockStringEquals($expectedBlockStringLines, $rawStringLines);
    }

    public function testRemovesBlankLeadingAndTrailingLines(): void
    {
        $rawStringLines = [
            '  ',
            '        ',
            '    Hello',
            '      World!',
            '',
            '    Yours,',
            '      GraphQL.',
            '        ',
            '  ',
        ];

        $expectedBlockStringLines = [
            'Hello',
            '  World!',
            '',
            'Yours,',
            '  GraphQL.'
        ];

        $this->assertBlockStringEquals($expectedBlockStringLines, $rawStringLines);
    }

    public function testRetainsIndentationFromFirstLine(): void
    {
        $rawStringLines = [
            '    Hello',
            '      World!',
            '',
            '    Yours,',
            '      GraphQL.',
            '        ',
            '  ',
        ];

        $expectedBlockStringLines = [
            '    Hello',
            '  World!',
            '',
            'Yours,',
            '  GraphQL.'
        ];

        $this->assertBlockStringEquals($expectedBlockStringLines, $rawStringLines);
    }

    public function testDoesNotAlterTrailingSpaces(): void
    {
        $rawStringLines = [
            '               ',
            '    Hello,     ',
            '      World!   ',
            '               ',
            '    Yours,     ',
            '      GraphQL. ',
            '               ',
        ];

        $expectedBlockStringLines = [
            'Hello,     ',
            '  World!   ',
            '           ',
            'Yours,     ',
            '  GraphQL. ',
        ];

        $this->assertBlockStringEquals($expectedBlockStringLines, $rawStringLines);
    }

    /**
     * @param array $expectedBlockStringLines
     * @param array $rawStringLines
     */
    private function assertBlockStringEquals(array $expectedBlockStringLines, array $rawStringLines): void
    {
        $actualBlockString = blockStringValue(implode("\n", $rawStringLines));

        $this->assertSame(implode("\n", $expectedBlockStringLines), $actualBlockString);
    }
}
