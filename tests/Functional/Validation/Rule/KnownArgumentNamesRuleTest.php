<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Language\locationShorthandToArray;
use Digia\GraphQL\Validation\Rule\KnownArgumentNamesRule;
use function Digia\GraphQL\Validation\Rule\unknownArgumentMessage;
use function Digia\GraphQL\Validation\Rule\unknownDirectiveArgumentMessage;

function unknownArgument($argumentName, $fieldName, $typeName, $suggestedArguments, $location)
{
    return [
        'message'   => unknownArgumentMessage($argumentName, $fieldName, $typeName, $suggestedArguments),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

function unknownDirectiveArgument($argumentName, $directiveName, $suggestedArguments, $location)
{
    return [
        'message'   => unknownDirectiveArgumentMessage($argumentName, $directiveName, $suggestedArguments),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

class KnownArgumentNamesRuleTest extends RuleTestCase
{
    public function testSingleArgumentIsKnown()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            '
fragment argOnRequiredArg on Dog {
  doesKnownCommand(dogCommand: SIT)
}
'
        );
    }

    public function testMultipleArgumentsAreKnown()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            '
fragment multipleArgs on ComplicatedArgs {
  multipleReqs(req1: 1, req2: 2)
}
'
        );
    }

    public function testIgnoresArgumentsOfUnknownFields()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            '
fragment argOnUnknownField on Dog {
  unknownField(unknownArg: SIT)
}
'
        );
    }

    public function testMultipleArgumentsInReverseOrderAreKnown()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            '
fragment multipleArgsReverseOrder on ComplicatedArgs {
  multipleReqs(req2: 2, req1: 1)
}
'
        );
    }

    public function testNoArgumentsOnOptionalArguments()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            '
fragment noArgOnOptionalArg on Dog {
  isHouseTrained
}
'
        );
    }

    public function testArgumentsAreKnownDeeply()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            '
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
            '
        );
    }

    public function testDirectiveArgumentsAreKnown()
    {
        $this->expectPassesRule(
            new KnownArgumentNamesRule(),
            '
{
  dog @skip(if: true)
}
'
        );
    }

    public function testUndirectArgumentsAreInvalid()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            '
{
  dog @skip(unless: true)
}
',
            [unknownDirectiveArgument('unless', 'skip', [], [3, 13])]
        );
    }

    public function testMisspelledDirectiveArgumentsAreReported()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            '
{
  dog @skip(iff: true)
}
',
            [unknownDirectiveArgument('iff', 'skip', ['if'], [3, 13])]
        );
    }

    public function testInvalidArgumentName()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            '
fragment invalidArgName on Dog {
  doesKnowCommand(unknown: true)
}
',
            [unknownArgument('unknown', 'doesKnowCommand', 'Dog', [], [3, 19])]
        );
    }

    public function testMisspelledArgumentNameIsReported()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            '
fragment invalidArgName on Dog {
  doesKnowCommand(dogcommand: true)
}
',
            [unknownArgument('dogcommand', 'doesKnowCommand', 'Dog', ['dogCommand'], [3, 19])]
        );
    }

    public function testUnknownArgumentsAmongstKnownArguments()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            '
fragment oneGoodArgOneInvalidArg on Dog {
  doesKnowCommand(whoknows: 1, dogCommand: SIT, unknown: true)
}
',
            [
                unknownArgument('whoknows', 'doesKnowCommand', 'Dog', [], [3, 19]),
                unknownArgument('unknown', 'doesKnowCommand', 'Dog', [], [3, 49]),
            ]
        );
    }

    public function testUnknownArgumentsDeeply()
    {
        $this->expectFailsRule(
            new KnownArgumentNamesRule(),
            '
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
',
            [
                unknownArgument('unknown', 'doesKnowCommand', 'Dog', [], [4, 21]),
                unknownArgument('unknown', 'doesKnowCommand', 'Dog', [], [9, 25]),
            ]
        );
    }
}
