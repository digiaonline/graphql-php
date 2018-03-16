<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\FragmentsOnCompositeTypesRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\fragmentOnNonComposite;

class FragmentsOnCompositeTypesRuleTest extends RuleTestCase
{
    protected function getRuleClassName(): string
    {
        return FragmentsOnCompositeTypesRule::class;
    }

    public function testObjectIsValidFragmentType()
    {
        $this->expectPassesRule(
            $this->rule,
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
            $this->rule,
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
            $this->rule,
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
            $this->rule,
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
            $this->rule,
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
            $this->rule,
            dedent('
            fragment scalarFragment on Boolean {
              bad
            }
            '),
            [fragmentOnNonComposite('scalarFragment', 'Boolean', [1, 28])]
        );
    }
}
