<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\UniqueArgumentNamesRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\duplicateArgument;

class UniqueArgumentNamesRuleTest extends RuleTestCase
{
    protected function getRuleClassName(): string
    {
        return UniqueArgumentNamesRule::class;
    }

    public function testNoArgumentsOnField()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field
            }
            ')
        );
    }

    public function testNoArgumentsOnDirective()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field @directive
            }
            ')
        );
    }

    public function testArgumentOnField()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field(arg: "value")
            }
            ')
        );
    }

    public function testArgumentOnDirective()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field @directive(arg: "value")
            }
            ')
        );
    }

    public function testSameArgumentOnTwoFields()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              one: field(arg: "value")
              two: field(arg: "value")
            }
            ')
        );
    }

    public function testSameArgumentOnFieldAndDirective()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field(arg: "value") @directive(arg: "value")
            }
            ')
        );
    }

    public function testSameArgumentOnTwoDirectives()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field @directive1(arg: "value") @directive2(arg: "value")
            }
            ')
        );
    }

    public function testMultipleFieldArguments()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field @directive(arg1: "value", arg2: "value", arg3: "value")
            }
            ')
        );
    }

    public function testDuplicateFieldArguments()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field(arg1: "value", arg1: "value")
            }
            '),
            [duplicateArgument('arg1', [[2, 9], [2, 24]])]
        );
    }

    public function testManyDuplicateFieldArguments()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field(arg1: "value", arg1: "value", arg1: "value")
            }
            '),
            [
                duplicateArgument('arg1', [[2, 9], [2, 24]]),
                duplicateArgument('arg1', [[2, 9], [2, 39]]),
            ]
        );
    }

    public function testDuplicateDirectiveArguments()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field @directive(arg1: "value", arg1: "value")
            }
            '),
            [duplicateArgument('arg1', [[2, 20], [2, 35]])]
        );
    }

    public function testManyDuplicateDirectiveArguments()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              field @directive(arg1: "value", arg1: "value", arg1: "value")
            }
            '),
            [
                duplicateArgument('arg1', [[2, 20], [2, 35]]),
                duplicateArgument('arg1', [[2, 20], [2, 50]]),
            ]
        );
    }
}
