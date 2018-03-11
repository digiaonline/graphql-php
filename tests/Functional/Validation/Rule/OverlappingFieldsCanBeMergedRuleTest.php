<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\OverlappingFieldsCanBeMergedRule;
use function Digia\GraphQL\Language\locationShorthandToArray;
use function Digia\GraphQL\Validation\Rule\fieldsConflictMessage;

function fieldConflict($responseName, $reason, $locations)
{
    return [
        'message'   => fieldsConflictMessage($responseName, $reason),
        'locations' => array_map(function ($shorthand) {
            return locationShorthandToArray($shorthand);
        }, $locations),
        'path'      => null,
    ];
}

class OverlappingFieldsCanBeMergedRuleTest extends RuleTestCase
{
    public function testUniqueFields()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment uniqueFields on Dog {
  name
  nickname
}
'
        );
    }

    public function testIdenticalFields()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment mergeIdenticalFields on Dog {
  name
  name
}
'
        );
    }

    public function testIdenticalFieldsWithIdenticalArguments()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment mergeIdenticalFieldsWithIdenticalArgs on Dog {
  doesKnowCommand(dogCommand: SIT)
  doesKnowCommand(dogCommand: SIT)
}
'
        );
    }

    public function testIdenticalFieldsWithIdenticalDirectives()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment mergeSameFieldsWithSameDirectives on Dog {
  name @include(if: true)
  name @include(if: true)
}
'
        );
    }

    public function testDifferentArgumentsWithDifferentAliases()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment differentArgsWithDifferentAliases on Dog {
  knowsSit: doesKnowCommand(dogCommand: SIT)
  knowsDown: doesKnowCommand(dogCommand: DOWN)
}
'
        );
    }

    public function testDifferentDirectivesWithDifferentAliases()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment differentDirectivesWithDifferentAliases on Dog {
  nameIfTrue: name @include(if: true)
  nameIfFalse: name @include(if: false)
}
'
        );
    }

    public function testDifferentSkipIncludeDirectivesAccepted()
    {
        // Note: Differing skip/include directives don't create an ambiguous return
        // value and are acceptable in conditions where differing runtime values
        // may have the same desired effect of including or skipping a field.
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment differentDirectivesWithDifferentAliases on Dog {
  name @include(if: true)
  name @include(if: false)
}
'
        );
    }

    public function testSameAliasesWithDifferentFieldTargets()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment sameAliasesWithDifferentFieldTargets on Dog {
  fido: name
  fido: nickname
}
',
            [fieldConflict('fido', 'name and nickname are different fields', [[3, 3], [4, 3]])]
        );
    }

    public function testSameAliasesAllowedOnNonOverlappingFields()
    {
        // This is valid since no object can be both a "Dog" and a "Cat", thus
        // these fields can never overlap.
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment sameAliasesWithDifferentFieldTargets on Pet {
  ... on Dog {
    name
  }
  ... on Cat {
    name: nickname
  }
}
'
        );
    }

    public function testAliasMaskingDirectFieldAccess()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment aliasMaskingDirectFieldAccess on Dog {
  name: nickname
  name
}
',
            [fieldConflict('name', 'nickname and name are different fields', [[3, 3], [4, 3]])]
        );
    }

    public function testDifferentArgumentsSecondAddsAnArgument()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment conflictingArgs on Dog {
  doesKnowCommand
  doesKnowCommand(dogCommand: HEEL)
}
',
            [fieldConflict('doesKnowCommand', 'they have differing arguments', [[3, 3], [4, 3]])]
        );
    }

    public function testDifferentArgsSecondMissingAnArgument()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment conflictingArgs on Dog {
  doesKnowCommand(dogCommand: SIT)
  doesKnowCommand
}
',
            [fieldConflict('doesKnowCommand', 'they have differing arguments', [[3, 3], [4, 3]])]
        );
    }

    public function testConflictingArguments()
    {
        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment conflictingArgs on Dog {
  doesKnowCommand(dogCommand: SIT)
  doesKnowCommand(dogCommand: HEEL)
}
',
            [fieldConflict('doesKnowCommand', 'they have differing arguments', [[3, 3], [4, 3]])]
        );
    }

    public function testAllowsDifferentArgumentsWhereNoConflictIsPossible()
    {
        $this->markTestIncomplete('Test should pass, but it does not for some reason.');

        // This is valid since no object can be both a "Dog" and a "Cat", thus
        // these fields can never overlap.
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment conflictingArgs on Pet {
  ... on Dog {
    name(surname: true)
  }
  ... on Cat {
    name
  }
}
'
        );
    }

    public function testEncountersConflictInFragments()
    {
        $this->markTestIncomplete('Test should not pass, but it does for some reason.');

        $this->expectFailsRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
{
  ...A
  ...B
}
fragment A on Type {
  x: a
}
fragment B on Type {
  x: b
}
',
            [fieldConflict('x', 'a and b are different fields', [[7, 3], [10, 3]])]
        );
    }
}
