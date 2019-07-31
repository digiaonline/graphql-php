<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\ProvidedRequiredArgumentsRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\missingDirectiveArgument;
use function Digia\GraphQL\Test\Functional\Validation\missingFieldArgument;

class ProvidedRequiredArgumentsRuleTest extends RuleTestCase
{
    protected function getRuleClassName(): string
    {
        return ProvidedRequiredArgumentsRule::class;
    }

    public function testIgnoresUnknownArguments()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              dog {
                isHouseTrained(unknownArgument: true)
              }
            }
            '
        );
    }

    public function testValidNonNullableValue()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              dog {
                isHouseTrained(atOtherHomes: true)
              }
            }
            '
        );
    }

    public function testNoArgumentOnOptionalArgument()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              dog {
                isHouseTrained
              }
            }
            '
        );
    }

    public function testNoArgumentOnNonNullFieldWithDefaultValue()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              complicatedArgs {
                nonNullFieldWithDefault
              }
            }
            '
        );
    }

    public function testMultipleArguments()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleReqs(req1: 1, req2: 2)
              }
            }
            '
        );
    }

    public function testMultipleArgumentsInReverseOrder()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleReqs(req2: 2, req1: 1)
              }
            }
            '
        );
    }

    public function testNoArgumentsOnMultipleOptional()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleOpts
              }
            }
            '
        );
    }

    public function testOneArgumentOnMultipleOptional()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleOpts(opt1: 1)
              }
            }
            '
        );
    }

    public function testSecondArgumentOnMultipleOptional()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleOpts(opt2: 1)
              }
            }
            '
        );
    }

    public function testMultipleRequiredOnMixedList()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleOptAndReq(req1: 3, req2: 4)
              }
            }
            '
        );
    }

    public function testMultipleRequiredAndOneOptionalOnMixedList()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleOptAndReq(req1: 3, req2: 4, opt1: 5)
              }
            }
            '
        );
    }

    public function testAllRequiredAndOptinalOnMixedList()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleOptAndReq(req1: 3, req2: 4, opt1: 5, opt2: 6)
              }
            }
            '
        );
    }

    public function testMissingOneNonNullableArgument()
    {
        $this->expectFailsRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleReqs(req2: 2)
              }
            }
            ',
            [missingFieldArgument('multipleReqs', 'req1', 'Int!', [3, 5])]
        );
    }

    public function testMultipleNonNullableArguments()
    {
        $this->expectFailsRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleReqs
              }
            }
            ',
            [
                missingFieldArgument('multipleReqs', 'req1', 'Int!', [3, 5]),
                missingFieldArgument('multipleReqs', 'req2', 'Int!', [3, 5]),
            ]
        );
    }

    public function testIncorrectValueAndMissingArgument()
    {
        $this->expectFailsRule(
            $this->rule,
            '
            {
              complicatedArgs {
                multipleReqs(req1: "one")
              }
            }
            ',
            [missingFieldArgument('multipleReqs', 'req2', 'Int!', [3, 5])]
        );
    }

    public function testIgnoresUnknownDirectives()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              dog @unknown
            }
            '
        );
    }

    public function testDirectivesOfValidTypes()
    {
        $this->expectPassesRule(
            $this->rule,
            '
            {
              dog @include(if: true) {
                name
              }
              human @skip(if: false) {
                name
              }
            }
            '
        );
    }

    public function testDirectiveWithMissingTypes()
    {
        $this->expectFailsRule(
            $this->rule,
            '
            {
              dog @include {
                name @skip
              }
            }
            ',
            [
                missingDirectiveArgument('include', 'if', 'Boolean!', [2, 7]),
                missingDirectiveArgument('skip', 'if', 'Boolean!', [3, 10]),
            ]
        );
    }
}
