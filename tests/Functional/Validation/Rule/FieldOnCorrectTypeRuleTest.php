<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\FieldOnCorrectTypeRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\undefinedField;

class FieldOnCorrectTypeRuleTest extends RuleTestCase
{
    protected function getRuleClassName(): string
    {
        return FieldOnCorrectTypeRule::class;
    }

    public function testObjectFieldSelection()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment objectFieldSelection on Dog {
              __typename
              name
            }
            ')
        );
    }

    public function testAliasedObjectFieldSelection()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment objectFieldSelection on Dog {
              otherName : name
            }
            ')
        );
    }


    public function testInterfaceFieldSelection()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment interfaceFieldSelection on Pet {
              __typename
              name
            }
            ')
        );
    }

    public function testAliasedInterfaceFieldSelection()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment interfaceFieldSelection on Pet {
              otherName : name
            }
            ')
        );
    }

    public function testLyingAliasSelection()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment lyingAliasSelection on Dog {
              name : nickname
            }
            ')
        );
    }

    public function testIgnoresFieldOnUnknownType()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment unknownSelection on UnknownType {
              unknownField
            }
            ')
        );
    }

    public function testReportsErrorWhenTypeIsKnownAgain()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment typeKnownAgain on Pet {
              unknown_pet_field {
                ... on Cat {
                  unknown_cat_field
                }
              }
            }
            '),
            [
                undefinedField('unknown_pet_field', 'Pet', [], [], [2, 3]),
                undefinedField('unknown_cat_field', 'Cat', [], [], [4, 7]),
            ]
        );
    }

    public function testFieldNotDefinedOnFragment()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment fieldNotDefined on Dog {
              meowVolume
            }
            '),
            [undefinedField('meowVolume', 'Dog', [], ['barkVolume'], [2, 3])]
        );
    }

    public function testIgnoresDeeplyUnknownField()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment deepFieldNotDefined on Dog {
              unknown_field {
                deeper_unknown_field
              }
            }
            '),
            [undefinedField('unknown_field', 'Dog', [], [], [2, 3])]
        );
    }

    public function testSubFieldNotDefined()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment subFieldNotDefined on Human {
              pets {
                unknown_field
              }
            }
            '),
            [undefinedField('unknown_field', 'Pet', [], [], [3, 5])]
        );
    }

    public function testFieldNotDefinedOnInlineFragment()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment fieldNotDefined on Pet {
              ... on Dog {
                meowVolume
              }
            }
            '),
            [undefinedField('meowVolume', 'Dog', [], ['barkVolume'], [3, 5])]
        );
    }

    public function testAliasedFieldTargetNotDefined()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment aliasedFieldTargetNotDefined on Dog {
              volume: mooVolume
            }
            '),
            [undefinedField('mooVolume', 'Dog', [], ['barkVolume'], [2, 3])]
        );
    }

    public function testAliasedLyingFieldTargetNotDefined()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment aliasedLyingFieldTargetNotDefined on Dog {
              barkVolume: kawVolume
            }
            '),
            [undefinedField('kawVolume', 'Dog', [], ['barkVolume'], [2, 3])]
        );
    }

    public function testNotDefinedOnInterface()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment notDefinedOnInterface on Pet {
              tailLength
            }
            '),
            [undefinedField('tailLength', 'Pet', [], [], [2, 3])]
        );
    }

    public function testDefinedOnImplementorsButNotOnInterface()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment definedOnImplementorsButNotInterface on Pet {
              nickname
            }
            '),
            [undefinedField('nickname', 'Pet', ['Dog', 'Cat'], ['name'], [2, 3])]
        );
    }

    public function testMetaFieldSelectionOnUnion()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment directFieldSelectionOnUnion on CatOrDog {
              __typename
            }
            ')
        );
    }

    public function testDirectFieldSelectionOnUnion()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment directFieldSelectionOnUnion on CatOrDog {
              directField
            }
            '),
            [undefinedField('directField', 'CatOrDog', [], [], [2, 3])]
        );
    }

    public function testDefinedOnImplementorsQueriedOnUnion()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment definedOnImplementorsQueriedOnUnion on CatOrDog {
              name
            }
            '),
            [
                undefinedField(
                    'name',
                    'CatOrDog',
                    ['Being', 'Pet', 'Canine', 'Dog', 'Cat'],
                    [],
                    [2, 3]
                )
            ]
        );
    }

    public function testValidFieldInInlineFragment()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            fragment objectFieldSelection on Pet {
              ... on Dog {
                name
              }
              ... {
                name
              }
            }
            ')
        );
    }
}
