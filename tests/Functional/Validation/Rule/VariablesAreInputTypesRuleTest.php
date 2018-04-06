<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\VariablesAreInputTypesRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\nonInputTypeOnVariable;

class VariablesAreInputTypesRuleTest extends RuleTestCase
{

    public function testInputTypesAreValid()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Foo($a: String, $b: [Boolean!]!, $c: ComplexInput) {
              field(a: $a, b: $b, c: $c)
            }
            ')
        );
    }

    public function testOutputTypesAreInvalid()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Foo($a: Dog, $b: [[CatOrDog!]]!, $c: Pet) {
              field(a: $a, b: $b, c: $c)
            }
            '),
            [
                nonInputTypeOnVariable('a', 'Dog', [1, 15]),
                nonInputTypeOnVariable('b', '[[CatOrDog!]]!', [1, 24]),
                nonInputTypeOnVariable('c', 'Pet', [1, 44]),
            ]
        );
    }

    protected function getRuleClassName(): string
    {
        return VariablesAreInputTypesRule::class;
    }
}
