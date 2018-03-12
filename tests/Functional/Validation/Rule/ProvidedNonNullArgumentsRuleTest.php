<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\ProvidedNonNullArgumentsRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\missingDirectiveArgument;
use function Digia\GraphQL\Test\Functional\Validation\missingFieldArgument;

class ProvidedNonNullArgumentsRuleTest extends RuleTestCase
{
    public function testIgnoresUnknownArguments()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              dog {
                isHouseTrained(unknownArgument: true)
              }
            }
            ')
        );
    }

    public function testValidNonNullableValue()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              dog {
                isHouseTrained(atOtherHomes: true)
              }
            }
            ')
        );
    }

    public function testNoArgumentOnOptionalArgument()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              dog {
                isHouseTrained
              }
            }
            ')
        );
    }

    public function testMultipleArguments()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleReqs(req1: 1, req2: 2)
              }
            }
            ')
        );
    }

    public function testMultipleArgumentsInReverseOrder()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleReqs(req2: 2, req1: 1)
              }
            }
            ')
        );
    }

    public function testNoArgumentsOnMultipleOptional()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleOpts
              }
            }
            ')
        );
    }

    public function testOneArgumentOnMultipleOptional()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleOpts(opt1: 1)
              }
            }
            ')
        );
    }

    public function testSecondArgumentOnMultipleOptional()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleOpts(opt2: 1)
              }
            }
            ')
        );
    }

    public function testMultipleRequiredOnMixedList()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleOptAndReq(req1: 3, req2: 4)
              }
            }
            ')
        );
    }

    public function testMultipleRequiredAndOneOptionalOnMixedList()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleOptAndReq(req1: 3, req2: 4, opt1: 5)
              }
            }
            ')
        );
    }

    public function testAllRequiredAndOptinalOnMixedList()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleOptAndReq(req1: 3, req2: 4, opt1: 5, opt2: 6)
              }
            }
            ')
        );
    }

    public function testMissingOneNonNullableArgument()
    {
        $this->expectFailsRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleReqs(req2: 2)
              }
            }
            '),
            [missingFieldArgument('multipleReqs', 'req1', 'Int!', [3, 5])]
        );
    }

    public function testMultipleNonNullableArguments()
    {
        $this->expectFailsRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleReqs
              }
            }
            '),
            [
                missingFieldArgument('multipleReqs', 'req1', 'Int!', [3, 5]),
                missingFieldArgument('multipleReqs', 'req2', 'Int!', [3, 5]),
            ]
        );
    }

    public function testIncorrectValueAndMissingArgument()
    {
        $this->expectFailsRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              complicatedArgs {
                multipleReqs(req1: "one")
              }
            }
            '),
            [missingFieldArgument('multipleReqs', 'req2', 'Int!', [3, 5])]
        );
    }

    public function testIgnoresUnknownDirectives()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              dog @unknown
            }
            ')
        );
    }

    public function testDirectivesOfValidTypes()
    {
        $this->expectPassesRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              dog @include(if: true) {
                name
              }
              human @skip(if: false) {
                name
              }
            }
            ')
        );
    }

    public function testDirectiveWithMissingTypes()
    {
        $this->expectFailsRule(
            new ProvidedNonNullArgumentsRule(),
            dedent('
            {
              dog @include {
                name @skip
              }
            }
            '),
            [
                missingDirectiveArgument('include', 'if', 'Boolean!', [2, 7]),
                missingDirectiveArgument('skip', 'if', 'Boolean!', [3, 10]),
            ]
        );
    }
}
