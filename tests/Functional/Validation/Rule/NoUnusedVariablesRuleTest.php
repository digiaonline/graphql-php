<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Language\dedent;
use Digia\GraphQL\Validation\Rule\NoUnusedVariablesRule;
use function Digia\GraphQL\Test\Functional\Validation\unusedVariable;

class NoUnusedVariablesRuleTest extends RuleTestCase
{
    public function testUsesAllVariables()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
            dedent('
            query ($a: String, $b: String, $c: String) {
              field(a: $a, b: $b, c: $c)
            }
            ')
        );
    }

    public function testUsesAllVariablesDeeply()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
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

    public function testUsesAllVariablesDeeplyInInlineFragments()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
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

    public function testUsesAllVariablesInFragments()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
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

    public function testVariableUsedByFragmentInMultipleOperations()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
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

    public function testVariableUsedByRecursiveFragment()
    {
        $this->expectPassesRule(
            new NoUnusedVariablesRule(),
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

    public function testVariableNotUsed()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
            dedent('
            query ($a: String, $b: String, $c: String) {
              field(a: $a, b: $b)
            }
            '),
            [unusedVariable('c', null, [1, 32])]
        );
    }

    public function testMultipleVariablesNotUsed()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
            dedent('
            query Foo($a: String, $b: String, $c: String) {
              field(b: $b)
            }
            '),
            [
                unusedVariable('a', 'Foo', [1, 11]),
                unusedVariable('c', 'Foo', [1, 35]),
            ]
        );
    }

    public function testVariableNotUsedInFragments()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
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
              field
            }
            '),
            [unusedVariable('c', 'Foo', [1, 35])]
        );
    }

    public function testMultipleVariablesNotUsedInFragments()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
            dedent('
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
            '),
            [
                unusedVariable('a', 'Foo', [1, 11]),
                unusedVariable('c', 'Foo', [1, 35]),
            ]
        );
    }

    public function testVariableNotUsedByUnreferencedFragment()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
            dedent('
            query Foo($b: String) {
              ...FragA
            }
            fragment FragA on Type {
              field(a: $a)
            }
            fragment FragB on Type {
              field(b: $b)
            }
            '),
            [unusedVariable('b', 'Foo', [1, 11])]
        );
    }

    public function testVariableNotUsedByFragmentUsedByOtherOperation()
    {
        $this->expectFailsRule(
            new NoUnusedVariablesRule(),
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
                unusedVariable('b', 'Foo', [1, 11]),
                unusedVariable('a', 'Bar', [4, 11]),
            ]
        );
    }
}
