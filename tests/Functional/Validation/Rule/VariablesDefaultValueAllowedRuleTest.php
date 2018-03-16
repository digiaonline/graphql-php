<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\VariablesDefaultValueAllowedRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\variableDefaultValueNotAllowed;

class VariablesDefaultValueAllowedRuleTest extends RuleTestCase
{
    protected function getRuleClassName(): string
    {
        return VariablesDefaultValueAllowedRule::class;
    }

    public function testVariablesWithNoDefaultValues()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query NullableValues($a: Int, $b: String, $c: ComplexInput) {
              dog { name }
            }
            ')
        );
    }

    public function testRequiredVariablesWithoutDefaultValues()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query RequiredValues($a: Int!, $b: String!) {
              dog { name }
            }
            ')
        );
    }

    public function testVariablesWithValidDefaultValues()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query WithDefaultValues(
              $a: Int = 1,
              $b: String = "ok",
              $c: ComplexInput = { requiredField: true, intField: 3 }
            ) {
              dog { name }
            }
            ')
        );
    }

    public function testVariablesWithValidNullDefaultValues()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query WithDefaultValues(
              $a: Int = null,
              $b: String = null,
              $c: ComplexInput = { requiredField: true, intField: null }
            ) {
              dog { name }
            }
            ')
        );
    }

    public function testNoRequiredVariablesWithDefaultValues()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query UnreachableDefaultValues($a: Int! = 3, $b: String! = "default") {
              dog { name }
            }
            '),
            [
                variableDefaultValueNotAllowed('a', 'Int!', 'Int', [1, 43]),
                variableDefaultValueNotAllowed('b', 'String!', 'String', [1, 60]),
            ]
        );
    }

    public function testVariablesWithInvalidDefaultNullValues()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query WithDefaultValues($a: Int! = null, $b: String! = null) {
              dog { name }
            }
            '),
            [
                variableDefaultValueNotAllowed('a', 'Int!', 'Int', [1, 36]),
                variableDefaultValueNotAllowed('b', 'String!', 'String', [1, 56]),
            ]
        );
    }
}
