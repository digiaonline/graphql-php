<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\FieldOnCorrectTypeRule;
use function Digia\GraphQL\Validation\Rule\undefinedFieldMessage;

function undefinedField($field, $type, $suggestedTypes, $suggestsFields, $line, $column)
{
    return [
        'message'   => undefinedFieldMessage($field, $type, $suggestedTypes, $suggestsFields),
        // TODO: Add locations when support has been added to GraphQLError.
        'locations' => null, //[['line' => $line, 'column' => $column]],
        'path'      => null,
    ];
}

class FieldOnCorrectTypeRuleTest extends RuleTestCase
{
    public function testObjectFieldSelection()
    {
        $this->expectPassesRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment objectFieldSelection on Dog {
              __typename
              name
            }
            '
        );
    }

    public function testAliasedObjectFieldSelection()
    {
        $this->expectPassesRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment objectFieldSelection on Dog {
              otherName : name
            }
            '
        );
    }


    public function testInterfaceFieldSelection()
    {
        $this->expectPassesRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment interfaceFieldSelection on Pet {
              __typename
              name
            }
            '
        );
    }

    public function testAliasedInterfaceFieldSelection()
    {
        $this->expectPassesRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment interfaceFieldSelection on Pet {
              otherName : name
            }
            '
        );
    }

    public function testLyingAliasSelection()
    {
        $this->expectPassesRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment lyingAliasSelection on Dog {
              name : nickname
            }
            '
        );
    }

    public function testIgnoresFieldOnUnknownType()
    {
        $this->expectPassesRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment unknownSelection on UnknownType {
              unknownField
            }
            '
        );
    }

    public function testReportsErrorWhenTypeIsKnownAgain()
    {
        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment typeKnownAgain on Pet {
              unknown_pet_field {
                ... on Cat {
                  unknown_cat_field
                }
              }
            }
            ',
            [
                undefinedField('unknown_pet_field', 'Pet', [], [], 3, 9),
                undefinedField('unknown_cat_field', 'Cat', [], [], 5, 13),
            ]
        );
    }

    public function testFieldNotDefinedOnFragment()
    {
        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment fieldNotDefined on Dog {
              meowVolume
            }
            ',
            [undefinedField('meowVolume', 'Dog', [], ['barkVolume'], 3, 9)]
        );
    }

    public function testIgnoresDeeplyUnknownField()
    {
        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment deepFieldNotDefined on Dog {
              unknown_field {
                deeper_unknown_field
              }
            }
            ',
            [undefinedField('unknown_field', 'Dog', [], [], 3, 9)]
        );
    }

    public function testSubFieldNotDefined()
    {
        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment subFieldNotDefined on Human {
              pets {
                unknown_field
              }
            }
            ',
            [undefinedField('unknown_field', 'Pet', [], [], 4, 11)]
        );
    }

    public function testFieldNotDefinedOnInlineFragment()
    {
        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment fieldNotDefined on Pet {
              ... on Dog {
                meowVolume
              }
            }
            ',
            [undefinedField('meowVolume', 'Dog', [], ['barkVolume'], 4, 11)]
        );
    }

    public function testAliasedFieldTargetNotDefined()
    {
        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment aliasedFieldTargetNotDefined on Dog {
              volume: mooVolume
            }
            ',
            [undefinedField('mooVolume', 'Dog', [], ['barkVolume'], 4, 11)]
        );
    }

    public function testAliasedLyingFieldTargetNotDefined()
    {
        // TODO: Add expectedErrors
        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment aliasedLyingFieldTargetNotDefined on Dog {
              barkVolume: kawVolume
            }
            ',
            [undefinedField('kawVolume', 'Dog', [], ['barkVolume'], 3, 9)]
        );
    }

    public function testNotDefinedOnInterface()
    {
        // TODO: Add expectedErrors
        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment notDefinedOnInterface on Pet {
              tailLength
            }
            ',
            [undefinedField('tailLength', 'Pet', [], [], 3, 9)]
        );
    }

    public function testDefinedOnImplementorsButNotOnInterface()
    {
        // TODO: Add expectedErrors
        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment definedOnImplementorsButNotInterface on Pet {
              nickname
            }
            ',
            [undefinedField('nickname', 'Pet', ['Dog', 'Cat'], ['name'], 3, 9)]
        );
    }

    public function testMetaFieldSelectionOnUnion()
    {
        $this->expectPassesRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment directFieldSelectionOnUnion on CatOrDog {
              __typename
            }
            '
        );
    }

    public function testDirectFieldSelectionOnUnion()
    {
        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment directFieldSelectionOnUnion on CatOrDog {
              directField
            }
            ',
            [undefinedField('directField', 'CatOrDog', [], [], 3, 9)]
        );
    }

    public function testDefinedOnImplementorsQueriedOnUnion()
    {
        $this->markTestIncomplete(
            'Test taken from the reference implementation goes against all logic because Cat is defined before Dog.'
        );

        $this->expectFailsRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment definedOnImplementorsQueriedOnUnion on CatOrDog {
              name
            }
            ',
            [
                undefinedField(
                    'name',
                    'CatOrDog',
                    ['Being', 'Pet', 'Canine', 'Dog', 'Cat'],
                    [],
                    3,
                    9
                )
            ]
        );
    }

    public function testValidFieldInInlineFragment()
    {
        $this->expectPassesRule(
            new FieldOnCorrectTypeRule(),
            '
            fragment objectFieldSelection on Pet {
              ... on Dog {
                name
              }
              ... {
                name
              }
            }
            '
        );
    }
}
