<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\UniqueInputFieldNamesRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\duplicateInputField;

class UniqueInputFieldNamesRuleTest extends RuleTestCase
{

    public function testInputObjectWithFields()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field(arg: { f: true })
            }
            ')
        );
    }

    public function testSameInputObjectWithTwoArguments()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field(arg1: { f: true }, arg2: { f: true })
            }
            ')
        );
    }

    public function testMultipleInputObjectFields()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field(arg: { f1: "value", f2: "value", f3: "value" })
            }
            ')
        );
    }

    public function testAllowsForNestedInputObjectsWithSimilarFields()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field(arg: {
                deep: {
                  deep: {
                    id: 1
                  }
                  id: 1
                }
                id: 1
              })
            }
            ')
        );
    }

    public function testDuplicateInputObjectFields()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field(arg: { f1: "value", f1: "value" })
            }
            '),
            [duplicateInputField('f1', [[2, 16], [2, 29]])]
        );
    }

    public function testManyDuplicateInputObjectFields()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field(arg: { f1: "value", f1: "value", f1: "value" })
            }
            '),
            [
                duplicateInputField('f1', [[2, 16], [2, 29]]),
                duplicateInputField('f1', [[2, 16], [2, 42]]),
            ]
        );
    }

    protected function getRuleClassName(): string
    {
        return UniqueInputFieldNamesRule::class;
    }
}
