<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\VariablesInAllowedPositionRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\badVariablePosition;

class VariablesInAllowedPositionRuleTest extends RuleTestCase
{

    public function testBooleanAllowsBoolean()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($booleanArg: Boolean)
            {
              complicatedArgs {
                booleanArgField(booleanArg: $booleanArg)
              }
            }
            ')
        );
    }

    public function testBooleanAllowsBooleanWithinFragment()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment booleanArgFrag on ComplicatedArgs {
              booleanArgField(booleanArg: $booleanArg)
            }
            query Query($booleanArg: Boolean) {
              complicatedArgs {
                ...booleanArgFrag
              }
            }
            ')
        );

        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($booleanArg: Boolean) {
              complicatedArgs {
                ...booleanArgFrag
              }
            }
            fragment booleanArgFrag on ComplicatedArgs {
              booleanArgField(booleanArg: $booleanArg)
            }
            ')
        );
    }

    public function testBooleanAllowsNonNullBoolean()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($nonNullBooleanArg: Boolean!) {
              complicatedArgs {
                booleanArgField(booleanArg: $nonNullBooleanArg)
              }
            }
            ')
        );
    }

    public function testNonNullBooleanAllowsBooleanWithinFragment()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment booleanArgFrag on ComplicatedArgs {
              booleanArgField(booleanArg: $nonNullBooleanArg)
            }
            
            query Query($nonNullBooleanArg: Boolean!) {
              complicatedArgs {
                ...booleanArgFrag
              }
            }
            ')
        );
    }

    public function testNonNullIntAllowsIntWithDefaultValue()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($intArg: Int = 1) {
              complicatedArgs {
                nonNullIntArgField(nonNullIntArg: $intArg)
              }
            }
            ')
        );
    }

    public function testListOfStringsAllowsListOfStrings()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($stringListVar: [String]) {
              complicatedArgs {
                stringListArgField(stringListArg: $stringListVar)
              }
            }
            ')
        );
    }

    public function testListOfNonNullStringsAllowsListOfStrings()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($stringListVar: [String!]) {
              complicatedArgs {
                stringListArgField(stringListArg: $stringListVar)
              }
            }
            ')
        );
    }

    public function testStringAllowsListOfStrings()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($stringVar: String) {
              complicatedArgs {
                stringListArgField(stringListArg: [$stringVar])
              }
            }
            ')
        );
    }

    public function testNonNullStringAllowsListOfStrings()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($stringVar: String!) {
              complicatedArgs {
                stringListArgField(stringListArg: [$stringVar])
              }
            }
            ')
        );
    }

    public function testComplexInputAllowsComplexInput()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($complexVar: ComplexInput) {
              complicatedArgs {
                complexArgField(complexArg: $complexVar)
              }
            }
            ')
        );
    }

    public function testComplexInputAllowsComplexInputInField()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($boolVar: Boolean = false) {
              complicatedArgs {
                complexArgField(complexArg: {requiredArg: $boolVar})
              }
            }
            ')
        );
    }

    public function testNonNullBooleanAllowsNonNullBooleanInDirective()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($boolVar: Boolean!) {
              dog @include(if: $boolVar)
            }
            ')
        );
    }

    public function testBooleanAllowsNonNullBooleanInDirectiveWithDefaultValue()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Query($boolVar: Boolean = false) {
              dog @include(if: $boolVar)
            }
            ')
        );
    }

    public function testIntDisallowsNonNullInt()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Query($intArg: Int) {
              complicatedArgs {
                nonNullIntArgField(nonNullIntArg: $intArg)
              }
            }
            '),
            [badVariablePosition('intArg', 'Int', 'Int!', [[1, 13], [3, 39]])]
        );
    }

    public function testIntDisallowsNonNullIntWithinFragment()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment nonNullIntArgFieldFrag on ComplicatedArgs {
              nonNullIntArgField(nonNullIntArg: $intArg)
            }
            
            query Query($intArg: Int) {
              complicatedArgs {
                ...nonNullIntArgFieldFrag
              }
            }
            '),
            [badVariablePosition('intArg', 'Int', 'Int!', [[5, 13], [2, 37]])]
        );
    }

    public function testIntDisallowsNonNullIntWithinNestedFragment()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment outerFrag on ComplicatedArgs {
              ...nonNullIntArgFieldFrag
            }
            
            fragment nonNullIntArgFieldFrag on ComplicatedArgs {
              nonNullIntArgField(nonNullIntArg: $intArg)
            }
            
            query Query($intArg: Int) {
              complicatedArgs {
                ...outerFrag
              }
            }
            '),
            [badVariablePosition('intArg', 'Int', 'Int!', [[9, 13], [6, 37]])]
        );
    }

    public function testBooleanDisallowsString()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Query($stringVar: String) {
              complicatedArgs {
                booleanArgField(booleanArg: $stringVar)
              }
            }
            '),
            [
                badVariablePosition('stringVar', 'String', 'Boolean',
                    [[1, 13], [3, 33]]),
            ]
        );
    }

    public function testStringDisallowsListOfStrings()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Query($stringVar: String) {
              complicatedArgs {
                stringListArgField(stringListArg: $stringVar)
              }
            }
            '),
            [
                badVariablePosition('stringVar', 'String', '[String]',
                    [[1, 13], [3, 39]]),
            ]
        );
    }

    public function testBooleanDisallowsNonNullBooleanInDirective()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Query($boolVar: Boolean) {
              dog @include(if: $boolVar)
            }
            '),
            [
                badVariablePosition('boolVar', 'Boolean', 'Boolean!',
                    [[1, 13], [2, 20]]),
            ]
        );
    }

    public function testStringDisallowsNonNullBooleanInDirective()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Query($stringVar: String) {
              dog @include(if: $stringVar)
            }
            '),
            [
                badVariablePosition('stringVar', 'String', 'Boolean!',
                    [[1, 13], [2, 20]]),
            ]
        );
    }

    public function testStringDisallowsListOfNonNullStrings()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Query($stringListVar: [String]) {
              complicatedArgs {
                stringListNonNullArgField(stringListNonNullArg: $stringListVar)
              }
            }
            '),
            [
                badVariablePosition('stringListVar', '[String]', '[String!]',
                    [[1, 13], [3, 53]]),
            ]
        );
    }

    protected function getRuleClassName(): string
    {
        return VariablesInAllowedPositionRule::class;
    }
}
