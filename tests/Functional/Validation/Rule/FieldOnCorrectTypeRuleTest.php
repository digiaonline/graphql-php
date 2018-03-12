<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Language\dedent;
use Digia\GraphQL\Validation\Rule\FieldOnCorrectTypeRule;
use function Digia\GraphQL\Test\Functional\Validation\undefinedField;

class FieldOnCorrectTypeRuleTest extends RuleTestCase
{
    public function testObjectFieldSelection()
    {
        $this->expectPassesRule(
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
        $this->markTestIncomplete(
            'POTENTIAL BUG: Test taken from the reference implementation goes against all logic because Cat is defined before Dog.'
        );

        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
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
            new FieldOnCorrectTypeRule(),
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
