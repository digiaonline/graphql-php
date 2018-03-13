<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Language\dedent;
use Digia\GraphQL\Validation\Rule\FragmentsOnCompositeTypesRule;
use function Digia\GraphQL\Test\Functional\Validation\fragmentOnNonComposite;

class FragmentsOnCompositeTypesRuleTest extends RuleTestCase
{
    public function testObjectIsValidFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            dedent('
            fragment validFragment on Dog {
              barks
            }
            ')
        );
    }

    public function testInterfaceIsValidFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            dedent('
            fragment validFragment on Pet {
              name
            }
            ')
        );
    }

    public function testObjectIsValidInlineFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            dedent('
            fragment validFragment on Pet {
              ... on Dog {
                barks
              }
            }
            ')
        );
    }

    public function testInlineFragmentWithoutTypeIsValid()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            dedent('
            fragment validFragment on Pet {
              ... {
                name
              }
            }
            ')
        );
    }

    public function testUnionIsValidFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            dedent('
            fragment validFragment on CatOrDog {
              __typename
            }
            ')
        );
    }

    public function testScalarIsInvalidFragmentType()
    {
        $this->expectFailsRule(
            new FragmentsOnCompositeTypesRule(),
            dedent('
            fragment scalarFragment on Boolean {
              bad
            }
            '),
            [fragmentOnNonComposite('scalarFragment', 'Boolean', [1, 28])]
        );
    }
}
