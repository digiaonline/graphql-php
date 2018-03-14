<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Error\GraphQLException;
use function Digia\GraphQL\Test\Functional\Validation\requiredField;
use function Digia\GraphQL\Test\Functional\Validation\unknownField;
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

    public function testGoodListValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringListArgField(stringListArg: ["one", null, "two"])
              }
            }
            ')
        );
    }

    public function testEmptyListValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringListArgField(stringListArg: [])
              }
            }
            ')
        );
    }

    public function testNullValue()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringListArgField(stringListArg: null)
              }
            }
            ')
        );
    }

    public function testSingleValueIntoList()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringListArgField(stringListArg: "one")
              }
            }
            ')
        );
    }

    public function testInvalidListValueWithIncorrectItemType()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringListArgField(stringListArg: ["one", 2])
              }
            }
            '),
            [badValue('String', '2', [3, 47])]
        );
    }

    public function testIntValueIntoListOfStrings()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                stringListArgField(stringListArg: 1)
              }
            }
            '),
            [badValue('[String]', '1', [3, 39])]
        );
    }

    public function testValidNonNullableValueOnOptionalArgument()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              dog {
                isHouseTrained(atOtherHomes: true)
              }
            }
            ')
        );
    }

    public function testNoValueOnOptionalArgument()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
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
            new ValuesOfCorrectTypeRule(),
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
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                multipleReqs(req2: 2, req1: 1)
              }
            }
            ')
        );
    }

    public function testNoArgumentsOnMultipleOptionalArguments()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                multipleReqs
              }
            }
            ')
        );
    }

    public function testNoArgumentOnMultipleOptionalArguments()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                multipleOpts
              }
            }
            ')
        );
    }

    public function testOneArgumentOnMultipleOptionalArguments()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                multipleOpts(opt1: 1)
              }
            }
            ')
        );
    }

    public function testSecondArgumentOnMultipleOptionalArguments()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                multipleOpts(opt2: 1)
              }
            }
            ')
        );
    }

    public function testMultipleRequiredArgumentsOnMixedList()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                multipleOptAndReq(req1: 3, req2: 4)
              }
            }
            ')
        );
    }

    public function testAllRequiredAndOptionalArgumentsOnMixedList()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                multipleOptAndReq(req1: 3, req2: 4, opt1: 5, opt2: 6)
              }
            }
            ')
        );
    }

    public function testInvalidNonNullableValueWithIncorrectValueType()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                multipleReqs(req2: "two", req1: "one")
              }
            }
            '),
            [
                badValue('Int!', '"two"', [3, 24]),
                badValue('Int!', '"one"', [3, 37]),
            ]
        );
    }

    public function testIncorrectValueAndMissingArgument()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                multipleReqs(req1: "one")
              }
            }
            '),
            [badValue('Int!', '"one"', [3, 24])]
        );
    }

    public function testInvalidNonNullableValueWithNullValue()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                multipleReqs(req1: null)
              }
            }
            '),
            [badValue('Int!', 'null', [3, 24])]
        );
    }

    public function testOptionalArgumentsDespiteRequiredFieldInType()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                complexArgField
              }
            }
            ')
        );
    }

    public function testPartialObjectOnlyRequired()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                complexArgField(complexArg: { requiredField: true })
              }
            }
            ')
        );
    }

    public function testParitalObjectRequiredFieldCanBeFalsey()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                complexArgField(complexArg: { requiredField: false })
              }
            }
            ')
        );
    }

    public function testPartialObjectIncludingRequired()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                complexArgField(complexArg: { requiredField: true, intField: 4 })
              }
            }
            ')
        );
    }

    public function testFullObject()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                complexArgField(complexArg: {
                  requiredField: true,
                  intField: 4,
                  stringField: "foo",
                  booleanField: false,
                  stringListField: ["one", "two"]
                })
              }
            }
            ')
        );
    }

    public function testFullObjectWithFieldsInDifferentOrder()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                complexArgField(complexArg: {
                  stringListField: ["one", "two"],
                  booleanField: false,
                  requiredField: true,
                  stringField: "foo",
                  intField: 4,
                })
              }
            }
            ')
        );
    }

    public function testPartialObjectWithMissingRequiredArgument()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                complexArgField(complexArg: { intField: 4 })
              }
            }
            '),
            [requiredField('ComplexInput', 'requiredField', 'Boolean!', [3, 33])]
        );
    }

    public function testParitalObjectWithInvalidFieldType()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                complexArgField(complexArg: {
                  stringListField: ["one", 2],
                  requiredField: true,
                })
              }
            }
            '),
            [badValue('String', '2', [4, 32])]
        );
    }

    public function testPartialObjectUnknownFieldArgument()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              complicatedArgs {
                complexArgField(complexArg: {
                  requiredField: true,
                  unknownField: "value"
                })
              }
            }
            '),
            [unknownField('ComplexInput', 'unknownField', [5, 7], 'Did you mean intField or booleanField?')]
        );
    }

    public function testReportsOriginalErrorForCustomScalarWhichThrows()
    {
        /** @var GraphQLException[] $errors */
        $errors = $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              invalidArg(arg: 123)
            }
            '),
            [badValue('Invalid', '123', [2, 19], 'Invalid scalar is always invalid: 123')]
        );
        $this->assertEquals($errors[0]->getOriginalErrorMessage(), 'Invalid scalar is always invalid: 123');
    }

    public function testAllowsCustomScalarToAcceptComplexLiterals()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              test1: anyArg(arg: 123)
              test2: anyArg(arg: "abc")
              test3: anyArg(arg: [123, "abc"])
              test4: anyArg(arg: {deep: [123, "abc"]})
            }
            ')
        );
    }

    public function testDirectiveArgumentsWithDirectivesOfValidTypes()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
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

    public function testDirectiveArgumentsWithDirectiveWithIncorrectTypes()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            {
              dog @include(if: "yes") {
                name @skip(if: ENUM)
              }
            }
            '),
            [
                badValue('Boolean!', '"yes"', [2, 20]),
                badValue('Boolean!', 'ENUM', [3, 20]),
            ]
        );
    }

    public function testVariablesWithValidDefaultValues()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            query WithDefaultValues(
              $a: Int = 1,
              $b: String = "ok",
              $c: ComplexInput = { requiredField: true, intField: 3 }
            ) {
              dog { name }
            }
            ')
        );
    }

    public function testVariablesWithValidDefaultNullValues()
    {
        $this->expectPassesRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            query WithDefaultValues(
              $a: Int = null,
              $b: String = null,
              $c: ComplexInput = { requiredField: true, intField: null }
            ) {
              dog { name }
            }
            ')
        );
    }

    public function testVariablesWithInvalidDefaultNullValues()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            query WithDefaultValues(
              $a: Int! = null,
              $b: String! = null,
              $c: ComplexInput = { requiredField: null, intField: null }
            ) {
              dog { name }
            }
            '),
            [
                badValue('Int!', 'null', [2, 14]),
                badValue('String!', 'null', [3, 17]),
                badValue('Boolean!', 'null', [4, 39]),
            ]
        );
    }

    public function testVariablesWithInvalidDefaultValues()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            query InvalidDefaultValues(
              $a: Int = "one",
              $b: String = 4,
              $c: ComplexInput = "notverycomplex"
            ) {
              dog { name }
            }
            '),
            [
                badValue('Int', '"one"', [2, 13]),
                badValue('String', '4', [3, 16]),
                badValue('ComplexInput', '"notverycomplex"', [4, 22]),
            ]
        );
    }

    public function testVariablesWithComplexInvalidDefaultValues()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            query WithDefaultValues(
              $a: ComplexInput = { requiredField: 123, intField: "abc" }
            ) {
              dog { name }
            }
            '),
            [
                badValue('Boolean!', '123', [2, 39]),
                badValue('Int', '"abc"', [2, 54]),
            ]
        );
    }

    public function testComplexVariablesMissingRequiredField()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            query MissingRequiredField($a: ComplexInput = {intField: 3}) {
              dog { name }
            }
            '),
            [requiredField('ComplexInput', 'requiredField', 'Boolean!', [1, 47])]
        );
    }

    public function testListVariablesWithInvalidItem()
    {
        $this->expectFailsRule(
            new ValuesOfCorrectTypeRule(),
            dedent('
            query InvalidItem($a: [String] = ["one", 2]) {
              dog { name }
            }
            '),
            [badValue('String', '2', [1, 42])]
        );
    }
}
