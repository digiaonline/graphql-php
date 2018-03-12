<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Language\dedent;
use Digia\GraphQL\Validation\Rule\NoUndefinedVariablesRule;
use function Digia\GraphQL\Test\Functional\Validation\undefinedVariable;

class NoUndefinedVariablesRuleTest extends RuleTestCase
{
    public function testAllVariablesDefined()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
            dedent('
            query Foo($a: String, $b: String, $c: String) {
              field(a: $a, b: $b, c: $c)
            }
            ')
        );
    }

    public function testAllVariablesDeeplyDefined()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
            dedent('
            query Foo($a: String, $b: String, $c: String) {
              field(a: $a) {
                field(b: $b) {
                  field(c: $c)
                }
              }
            }
            ')
        );
    }

    public function testAllVariablesDeeplyInInlineFragmentsDefined()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
            dedent('
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
            ')
        );
    }

    public function testAllVariablesInFragmentsDeeplyDefined()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
            dedent('
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
            ')
        );
    }

    public function testVariablesWithinSingleFragmentDefinedInMultipleOperations()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
            dedent('
            query Foo($a: String) {
              ...FragA
            }
            query Bar($a: String) {
              ...FragA
            }
            fragment FragA on Type {
              field(a: $a)
            }
            ')
        );
    }

    public function testVariableWithinFragmentsDefinedInOperations()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
            dedent('
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
            ')
        );
    }

    public function testVariableWithinRecursiveFragmentDefined()
    {
        $this->expectPassesRule(
            new NoUndefinedVariablesRule(),
            dedent('
            query Foo($a: String) {
              ...FragA
            }
            fragment FragA on Type {
              field(a: $a) {
                ...FragA
              }
            }
            ')
        );
    }

    public function testVariableNotDefined()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            dedent('
            query Foo($a: String, $b: String, $c: String) {
              field(a: $a, b: $b, c: $c, d: $d)
            }
            '),
            [undefinedVariable('d', [2, 33], 'Foo', [1, 1])]
        );
    }

    public function testVariableNotDefinedByAnonymousQuery()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            dedent( '
            {
              field(a: $a)
            }
            '),
            [undefinedVariable('a', [2, 12], '', [1, 1])]
        );
    }

    public function testMultipleVariablesNotDefined()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            dedent('
            query Foo($b: String) {
              field(a: $a, b: $b, c: $c)
            }
            '),
            [
                undefinedVariable('a', [2, 12], 'Foo', [1, 1]),
                undefinedVariable('c', [2, 26], 'Foo', [1, 1]),
            ]
        );
    }

    public function testVariableInFragmentNotDefinedByAnonymousQuery()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            dedent('
            {
              ...FragA
            }
            fragment FragA on Type {
              field(a: $a)
            }
            '),
            [undefinedVariable('a', [5, 12], '', [1, 1])]
        );
    }

    public function testVariableInFragmentNotDefinedByOperation()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            dedent('
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
            '),
            [undefinedVariable('c', [15, 12], 'Foo', [1, 1])]
        );
    }

    public function testMultipleVariablesInFragmentsNotDefined()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            dedent('
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
            '),
            [
                undefinedVariable('a', [5, 12], 'Foo', [1, 1]),
                undefinedVariable('c', [15, 12], 'Foo', [1, 1]),
            ]
        );
    }

    public function testSingleVariableInFragmentNotDefinedByMultipleOperations()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            dedent('
            query Foo($a: String) {
              ...FragAB
            }
            query Bar($a: String) {
              ...FragAB
            }
            fragment FragAB on Type {
              field(a: $a, b: $b)
            }
            '),
            [
                undefinedVariable('b', [8, 19], 'Foo', [1, 1]),
                undefinedVariable('b', [8, 19], 'Bar', [4, 1]),
            ]
        );
    }

    public function testVariablesInFragmentNotDefinedByMultipleOperations()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            dedent('
            query Foo($b: String) {
              ...FragAB
            }
            query Bar($a: String) {
              ...FragAB
            }
            fragment FragAB on Type {
              field(a: $a, b: $b)
            }
            '),
            [
                undefinedVariable('a', [8, 12], 'Foo', [1, 1]),
                undefinedVariable('b', [8, 19], 'Bar', [4, 1]),
            ]
        );
    }

    public function testVariableInFragmentUsedByOtherOperation()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            dedent('
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
            '),
            [
                undefinedVariable('a', [8, 12], 'Foo', [1, 1]),
                undefinedVariable('b', [11, 12], 'Bar', [4, 1]),
            ]
        );
    }

    public function testMultipleUndefinedVariablesProduceMultipleErrors()
    {
        $this->expectFailsRule(
            new NoUndefinedVariablesRule(),
            dedent('
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
            '),
            [
                undefinedVariable('a', [8, 13], 'Foo', [1, 1]),
                undefinedVariable('a', [10, 13], 'Foo', [1, 1]),
                undefinedVariable('c', [13, 13], 'Foo', [1, 1]),
                undefinedVariable('b', [8, 20], 'Bar', [4, 1]),
                undefinedVariable('b', [10, 20], 'Bar', [4, 1]),
                undefinedVariable('c', [13, 13], 'Bar', [4, 1]),
            ]
        );
    }
}
