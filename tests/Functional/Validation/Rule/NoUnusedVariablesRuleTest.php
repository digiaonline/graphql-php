<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\NoUnusedVariablesRule;
use function Digia\GraphQL\Language\locationShorthandToArray;
use function Digia\GraphQL\Validation\unusedVariableMessage;

function unusedVariable($variableName, $operationName, $location)
{
    return [
        'message'   => unusedVariableMessage($variableName, $operationName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

class NoUnusedVariablesRuleTest extends RuleTestCase
{
    public function testUsesAllVariables()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
            '
query ($a: String, $b: String, $c: String) {
  field(a: $a, b: $b, c: $c)
}
'
        );
    }

    public function testUsesAllVariablesDeeply()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
            '
query Foo($a: String, $b: String, $c: String) {
  field(a: $a) {
    field(b: $b) {
      field(c: $c)
    }
  }
}
'
        );
    }

    public function testUsesAllVariablesDeeplyInInlineFragments()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
            '
query Foo($a: String, $b: String, $c: String) {
  ... on Type {
    field(a: $a) {
      field(b: $b) {
        ... on Type {
          field(c: $c)
        }
      }
    }
  }
}
'
        );
    }

    public function testUsesAllVariablesInFragments()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
            '
query Foo($a: String, $b: String, $c: String) {
  ...FragA
}
fragment FragA on Type {
  field(a: $a) {
    ...FragB
  }
}
fragment FragB on Type {
  field(b: $b) {
    ...FragC
  }
}
fragment FragC on Type {
  field(c: $c)
}
'
        );
    }

    public function testVariableUsedByFragmentInMultipleOperations()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
            '
query Foo($a: String) {
  ...FragA
}
query Bar($b: String) {
  ...FragB
}
fragment FragA on Type {
  field(a: $a)
}
fragment FragB on Type {
  field(b: $b)
}
'
        );
    }

    public function testVariableUsedByRecursiveFragment()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
            '
query Foo($a: String) {
  ...FragA
}
fragment FragA on Type {
  field(a: $a) {
    ...FragA
  }
}
'
        );
    }

    public function testVariableNotUsed()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
            '
query ($a: String, $b: String, $c: String) {
  field(a: $a, b: $b)
}
',
            [unusedVariable('c', null, [2, 32])]
        );
    }

    public function testMultipleVariablesNotUsed()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
            '
query Foo($a: String, $b: String, $c: String) {
  field(b: $b)
}
',
            [
                unusedVariable('a', 'Foo', [2, 11]),
                unusedVariable('c', 'Foo', [2, 35]),
            ]
        );
    }

    public function testVariableNotUsedInFragments()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
            '
query Foo($a: String, $b: String, $c: String) {
  ...FragA
}
fragment FragA on Type {
  field(a: $a) {
    ...FragB
  }
}
fragment FragB on Type {
  field(b: $b) {
    ...FragC
  }
}
fragment FragC on Type {
  field
}
',
            [unusedVariable('c', 'Foo', [2, 35])]
        );
    }

    public function testMultipleVariablesNotUsedInFragments()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
            '
query Foo($a: String, $b: String, $c: String) {
  ...FragA
}
fragment FragA on Type {
  field {
    ...FragB
  }
}
fragment FragB on Type {
  field(b: $b) {
    ...FragC
  }
}
fragment FragC on Type {
  field
}
',
            [
                unusedVariable('a', 'Foo', [2, 11]),
                unusedVariable('c', 'Foo', [2, 35]),
            ]
        );
    }

    public function testVariableNotUsedByUnreferencedFragment()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
            '
query Foo($b: String) {
  ...FragA
}
fragment FragA on Type {
  field(a: $a)
}
fragment FragB on Type {
  field(b: $b)
}
',
            [unusedVariable('b', 'Foo', [2, 11])]
        );
    }

    public function testVariableNotUsedByFragmentUsedByOtherOperation()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
            '
query Foo($b: String) {
  ...FragA
}
query Bar($a: String) {
  ...FragB
}
fragment FragA on Type {
  field(a: $a)
}
fragment FragB on Type {
  field(b: $b)
}
',
            [
                unusedVariable('b', 'Foo', [2, 11]),
                unusedVariable('a', 'Bar', [5, 11]),
            ]
        );
    }
}
