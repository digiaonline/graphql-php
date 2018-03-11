<?php

namespace Digia\GraphQL\Test\Functional\Validation;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\Validation\fieldsConflictMessage;
use function Digia\GraphQL\Validation\undefinedFieldMessage;

class MessagesTest extends TestCase
{
    public function testUndefinedFieldMessageWorksWithoutSuggestions()
    {
        $this->assertEquals(undefinedFieldMessage('f', 'T', [], []), 'Cannot query field "f" on type "T".');
    }

    public function testUndefinedFieldMessageWorksWithNoSmallNumbersOfTypeSuggestions()
    {
        $this->assertEquals(
            undefinedFieldMessage('f', 'T', ['A', 'B'], []),
            'Cannot query field "f" on type "T". Did you mean to use an inline fragment on "A" or "B"?'
        );
    }

    public function testUndefinedFieldMessageWorksWithNoSmallNumbersOfFieldSuggestions()
    {
        $this->assertEquals(
            undefinedFieldMessage('f', 'T', [], ['z', 'y']),
            'Cannot query field "f" on type "T". Did you mean "z" or "y"?'
        );
    }

    public function testUndefinedFieldMessageOnlyShowsOneSetOfSuggestionsAtATimePreferringTypes()
    {
        $this->assertEquals(
            undefinedFieldMessage('f', 'T', ['A', 'B'], ['z', 'y']),
            'Cannot query field "f" on type "T". Did you mean to use an inline fragment on "A" or "B"?'
        );
    }

    public function testUndefinedFieldMessageLimitsLotsOfTypeSuggestions()
    {
        $this->assertEquals(
            undefinedFieldMessage('f', 'T', ['A', 'B', 'C', 'D', 'E', 'F'], []),
            'Cannot query field "f" on type "T". Did you mean to use an inline fragment on "A", "B", "C", "D" or "E"?'
        );
    }

    public function testUndefinedFieldMessageLimitsLotsOfFieldSuggestions()
    {
        $this->assertEquals(
            undefinedFieldMessage('f', 'T', [], ['z', 'y', 'x', 'w', 'v', 'u']),
            'Cannot query field "f" on type "T". Did you mean "z", "y", "x", "w" or "v"?'
        );
    }

    public function testFieldConflictMessageContainsHintForAliasConflict()
    {
        $this->assertEquals(
            fieldsConflictMessage('x', 'a and b are different fields'),
            'Fields "x" conflict because a and b are different fields. Use different aliases on the fields to fetch both if this was intentional.'
        );
    }
}
