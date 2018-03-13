<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\ValuesOfCorrectTypeRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\badValue;

class ValuesOfCorrectTypeRuleTest extends RuleTestCase
{
    public function testGoodIntValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                intArgField(intArg: 2)
              }
            }
            ')
        );
    }

    public function testGoodNegativeIntValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                intArgField(intArg: -2)
              }
            }
            ')
        );
    }

    public function testGoodBooleanValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                booleanArgField(booleanArg: true)
              }
            }
            ')
        );
    }

    public function testGoodStringValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringArgField(stringArg: "foo")
              }
            }
            ')
        );
    }

    public function testGoodFloatValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                floatArgField(floatArg: 1.1)
              }
            }
            ')
        );
    }

    public function testGoodNegativeFloatValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                floatArgField(floatArg: -1.1)
              }
            }
            ')
        );
    }

    public function testIntIntoFloat()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                floatArgField(floatArg: 1)
              }
            }
            ')
        );
    }

    public function testStringIntoID()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                idArgField(idArg: "someIdString")
              }
            }
            ')
        );
    }

    public function testGoodEnumValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              dog {
                doesKnowCommand(dogCommand: SIT)
              }
            }
            ')
        );
    }

    public function testEnumWithUndefinedValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                enumArgField(enumArg: UNKNOWN)
              }
            }
            ')
        );
    }

    public function testEnumWithNullValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                enumArgField(enumArg: NO_FUR)
              }
            }
            ')
        );
    }

    public function testNullIntoNullableType()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                intArgField(intArg: null)
              }
            }
            ')
        );

        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              dog(a: null, b: null, c:{ requiredField: true, intField: null }) {
                name
              }
            }
            ')
        );
    }

    public function testIntIntoString()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringArgField(stringArg: 1)
              }
            }
            '),
            [badValue('String', '1', [3, 31])]
        );
    }

    public function testFloatIntoString()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringArgField(stringArg: 1.0)
              }
            }
            '),
            [badValue('String', '1.0', [3, 31])]
        );
    }

    public function testBooleanIntoString()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringArgField(stringArg: true)
              }
            }
            '),
            [badValue('String', 'true', [3, 31])]
        );
    }

    public function testUnquotedStringIntoString()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringArgField(stringArg: BAR)
              }
            }
            '),
            [badValue('String', 'BAR', [3, 31])]
        );
    }

    public function testStringIntoInt()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                intArgField(intArg: "3")

              }
            }
            '),
            [badValue('Int', '"3"', [3, 25])]
        );
    }

    public function testBigIntIntoInt()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                intArgField(intArg: 829384293849283498239482938)
              }
            }
            '),
            [badValue('Int', '829384293849283498239482938', [3, 25])]
        );
    }

    public function testUnquotedStringIntoInt()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                intArgField(intArg: FOO)
              }
            }
            '),
            [badValue('Int', 'FOO', [3, 25])]
        );
    }

    public function testSimpleFloatIntoInt()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                intArgField(intArg: 3.0)
              }
            }
            '),
            [badValue('Int', '3.0', [3, 25])]
        );
    }

    public function testFloatIntoInt()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                intArgField(intArg: 3.333)
              }
            }
            '),
            [badValue('Int', '3.333', [3, 25])]
        );
    }

    public function testStringIntoFloat()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                floatArgField(floatArg: "3.333")
              }
            }
            '),
            [badValue('Float', '"3.333"', [3, 29])]
        );
    }

    public function testBooleanIntoFloat()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                floatArgField(floatArg: true)
              }
            }
            '),
            [badValue('Float', 'true', [3, 29])]
        );
    }

    public function testUnquotedStringIntoFloat()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                floatArgField(floatArg: FOO)
              }
            }
            '),
            [badValue('Float', 'FOO', [3, 29])]
        );
    }

    public function testIntIntoBoolean()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                booleanArgField(booleanArg: 2)
              }
            }
            '),
            [badValue('Boolean', '2', [3, 33])]
        );
    }

    public function testFloatIntoBoolean()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                booleanArgField(booleanArg: 1.0)
              }
            }
            '),
            [badValue('Boolean', '1.0', [3, 33])]
        );
    }

    public function testStringIntoBoolean()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                booleanArgField(booleanArg: "true")
              }
            }
            '),
            [badValue('Boolean', '"true"', [3, 33])]
        );
    }

    public function testUnquotedStringIntoBoolean()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                booleanArgField(booleanArg: TRUE)
              }
            }
            '),
            [badValue('Boolean', 'TRUE', [3, 33])]
        );
    }

    public function testFloatIntoID()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                idArgField(idArg: 1.0)
              }
            }
            '),
            [badValue('ID', '1.0', [3, 23])]
        );
    }

    public function testBooleanIntoID()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                idArgField(idArg: true)
              }
            }
            '),
            [badValue('ID', 'true', [3, 23])]
        );
    }

    public function testUnquotedStringIntoID()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                idArgField(idArg: SOMETHING)
              }
            }
            '),
            [badValue('ID', 'SOMETHING', [3, 23])]
        );
    }

    public function testIntIntoEnum()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              dog {
                doesKnowCommand(dogCommand: 2)
              }
            }
            '),
            [badValue('DogCommand', '2', [3, 33])]
        );
    }

    public function testFloatIntoEnum()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              dog {
                doesKnowCommand(dogCommand: 1.0)
              }
            }
            '),
            [badValue('DogCommand', '1.0', [3, 33])]
        );
    }

    public function testStringIntoEnum()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              dog {
                doesKnowCommand(dogCommand: "SIT")
              }
            }
            '),
            [badValue('DogCommand', '"SIT"', [3, 33], 'Did you mean the enum value SIT?')]
        );
    }

    public function testBooleanIntoEnum()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              dog {
                doesKnowCommand(dogCommand: true)
              }
            }
            '),
            [badValue('DogCommand', 'true', [3, 33])]
        );
    }

    public function testEnumValueIntoEnum()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              dog {
                doesKnowCommand(dogCommand: JUGGLE)
              }
            }
            '),
            [badValue('DogCommand', 'JUGGLE', [3, 33])]
        );
    }

    public function testDifferentCaseEnumValueIntoEnum()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              dog {
                doesKnowCommand(dogCommand: sit)
              }
            }
            '),
            [badValue('DogCommand', 'sit', [3, 33], 'Did you mean the enum value SIT?')]
        );
    }
}
