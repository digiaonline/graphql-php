<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\KnownArgumentNamesRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\unknownArgument;
use function Digia\GraphQL\Test\Functional\Validation\unknownDirectiveArgument;

class KnownArgumentNamesRuleTest extends RuleTestCase
{
    public function testSingleArgumentIsKnown()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            dedent('
            fragment argOnRequiredArg on Dog {
              doesKnownCommand(dogCommand: SIT)
            }
            ')
        );
    }

    public function testMultipleArgumentsAreKnown()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            dedent('
            fragment multipleArgs on ComplicatedArgs {
              multipleReqs(req1: 1, req2: 2)
            }
            ')
        );
    }

    public function testIgnoresArgumentsOfUnknownFields()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            dedent('
            fragment argOnUnknownField on Dog {
              unknownField(unknownArg: SIT)
            }
            ')
        );
    }

    public function testMultipleArgumentsInReverseOrderAreKnown()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            dedent('
            fragment multipleArgsReverseOrder on ComplicatedArgs {
              multipleReqs(req2: 2, req1: 1)
            }
            ')
        );
    }

    public function testNoArgumentsOnOptionalArguments()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            dedent('
            fragment noArgOnOptionalArg on Dog {
              isHouseTrained
            }
            ')
        );
    }

    public function testArgumentsAreKnownDeeply()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            dedent('
            {
              dog {
                doesKnowCommand(dogCommand: SIT)
              }
              human {
                pet {
                  ... on Dog {
                    doesKnowCommand(dogCommand: SIT)
                  }
                }
              }
            }
            ')
        );
    }

    public function testDirectiveArgumentsAreKnown()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            dedent('
            {
              dog @skip(if: true)
            }
            ')
        );
    }

    public function testUndirectArgumentsAreInvalid()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            dedent('
            {
              dog @skip(unless: true)
            }
            '),
            [unknownDirectiveArgument('unless', 'skip', [], [2, 13])]
        );
    }

    public function testMisspelledDirectiveArgumentsAreReported()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            dedent('
            {
              dog @skip(iff: true)
            }
            '),
            [unknownDirectiveArgument('iff', 'skip', ['if'], [2, 13])]
        );
    }

    public function testInvalidArgumentName()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            dedent('
            fragment invalidArgName on Dog {
              doesKnowCommand(unknown: true)
            }
            '),
            [unknownArgument('unknown', 'doesKnowCommand', 'Dog', [], [2, 19])]
        );
    }

    public function testMisspelledArgumentNameIsReported()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            dedent('
            fragment invalidArgName on Dog {
              doesKnowCommand(dogcommand: true)
            }
            '),
            [unknownArgument('dogcommand', 'doesKnowCommand', 'Dog', ['dogCommand'], [2, 19])]
        );
    }

    public function testUnknownArgumentsAmongstKnownArguments()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            dedent('
            fragment oneGoodArgOneInvalidArg on Dog {
              doesKnowCommand(whoknows: 1, dogCommand: SIT, unknown: true)
            }
            '),
            [
                unknownArgument('whoknows', 'doesKnowCommand', 'Dog', [], [2, 19]),
                unknownArgument('unknown', 'doesKnowCommand', 'Dog', [], [2, 49]),
            ]
        );
    }

    public function testUnknownArgumentsDeeply()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            dedent('
            {
              dog {
                doesKnowCommand(unknown: true)
              }
              human {
                pet {
                  ... on Dog {
                    doesKnowCommand(unknown: true)
                  }
                }
              }
            }
            '),
            [
                unknownArgument('unknown', 'doesKnowCommand', 'Dog', [], [3, 21]),
                unknownArgument('unknown', 'doesKnowCommand', 'Dog', [], [8, 25]),
            ]
        );
    }
}
