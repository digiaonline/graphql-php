<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Test\Functional\Validation\nonInputTypeOnVariable;
use Digia\GraphQL\Validation\Rule\VariablesAreInputTypesRule;
use function Digia\GraphQL\Language\dedent;

class VariablesAreInputTypesRuleTest extends RuleTestCase
{
    public function testInputTypesAreValid()
    {
        $this->expectPassesRule(
            new VariablesAreInputTypesRule(),
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
            new VariablesAreInputTypesRule(),
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
}
