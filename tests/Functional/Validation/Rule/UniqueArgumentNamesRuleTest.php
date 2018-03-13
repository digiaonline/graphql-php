<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\UniqueArgumentNamesRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\duplicateArgument;

class UniqueArgumentNamesRuleTest extends RuleTestCase
{
    public function testNoArgumentsOnField()
    {
        $this->expectPassesRule(
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
            new UniqueArgumentNamesRule(),
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
