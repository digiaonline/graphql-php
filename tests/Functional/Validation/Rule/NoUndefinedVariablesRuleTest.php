<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\NoUndefinedVariablesRule;
use function Digia\GraphQL\Test\Functional\Validation\undefinedVariable;

class NoUndefinedVariablesRuleTest extends RuleTestCase
{
    public function testAllVariablesDefined()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
            '
query Foo($a: String, $b: String, $c: String) {
  field(a: $a, b: $b, c: $c)
}
'
        );
    }

    public function testAllVariablesDeeplyDefined()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
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

    public function testAllVariablesDeeplyInInlineFragmentsDefined()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
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

    public function testAllVariablesInFragmentsDeeplyDefined()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
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

    public function testVariablesWithinSingleFragmentDefinedInMultipleOperations()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
            '
query Foo($a: String) {
  ...FragA
}
query Bar($a: String) {
  ...FragA
}
fragment FragA on Type {
  field(a: $a)
}
'
        );
    }

    public function testVariableWithinFragmentsDefinedInOperations()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
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

    public function testVariableWithinRecursiveFragmentDefined()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
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

    public function testVariableNotDefined()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            '
query Foo($a: String, $b: String, $c: String) {
  field(a: $a, b: $b, c: $c, d: $d)
}
',
            [undefinedVariable('d', [3, 33], 'Foo', [2, 1])]
        );
    }

    public function testVariableNotDefinedByAnonymousQuery()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            '
{
  field(a: $a)
}
',
            [undefinedVariable('a', [3, 12], '', [2, 1])]
        );
    }

    public function testMultipleVariablesNotDefined()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            '
query Foo($b: String) {
  field(a: $a, b: $b, c: $c)
}
',
            [
                undefinedVariable('a', [3, 12], 'Foo', [2, 1]),
                undefinedVariable('c', [3, 26], 'Foo', [2, 1]),
            ]
        );
    }

    public function testVariableInFragmentNotDefinedByAnonymousQuery()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            '
{
  ...FragA
}
fragment FragA on Type {
  field(a: $a)
}
',
            [undefinedVariable('a', [6, 12], '', [2, 1])]
        );
    }

    public function testVariableInFragmentNotDefinedByOperation()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            '
query Foo($a: String, $b: String) {
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
',
            [undefinedVariable('c', [16, 12], 'Foo', [2, 1])]
        );
    }

    public function testMultipleVariablesInFragmentsNotDefined()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            '
query Foo($b: String) {
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
',
            [
                undefinedVariable('a', [6, 12], 'Foo', [2, 1]),
                undefinedVariable('c', [16, 12], 'Foo', [2, 1]),
            ]
        );
    }

    public function testSingleVariableInFragmentNotDefinedByMultipleOperations()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            '
query Foo($a: String) {
  ...FragAB
}
query Bar($a: String) {
  ...FragAB
}
fragment FragAB on Type {
  field(a: $a, b: $b)
}
',
            [
                undefinedVariable('b', [9, 19], 'Foo', [2, 1]),
                undefinedVariable('b', [9, 19], 'Bar', [5, 1]),
            ]
        );
    }

    public function testVariablesInFragmentNotDefinedByMultipleOperations()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            '
query Foo($b: String) {
  ...FragAB
}
query Bar($a: String) {
  ...FragAB
}
fragment FragAB on Type {
  field(a: $a, b: $b)
}
',
            [
                undefinedVariable('a', [9, 12], 'Foo', [2, 1]),
                undefinedVariable('b', [9, 19], 'Bar', [5, 1]),
            ]
        );
    }

    public function testVariableInFragmentUsedByOtherOperation()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
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
                undefinedVariable('a', [9, 12], 'Foo', [2, 1]),
                undefinedVariable('b', [12, 12], 'Bar', [5, 1]),
            ]
        );
    }

    public function testMultipleUndefinedVariablesProduceMultipleErrors()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            '
query Foo($b: String) {
  ...FragAB
}
query Bar($a: String) {
  ...FragAB
}
fragment FragAB on Type {
  field1(a: $a, b: $b)
  ...FragC
  field3(a: $a, b: $b)
}
fragment FragC on Type {
  field2(c: $c)
}
',
            [
                undefinedVariable('a', [9, 13], 'Foo', [2, 1]),
                undefinedVariable('a', [11, 13], 'Foo', [2, 1]),
                undefinedVariable('c', [14, 13], 'Foo', [2, 1]),
                undefinedVariable('b', [9, 20], 'Bar', [5, 1]),
                undefinedVariable('b', [11, 20], 'Bar', [5, 1]),
                undefinedVariable('c', [14, 13], 'Bar', [5, 1]),
            ]
        );
    }
}
