<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\UniqueFragmentNamesRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\duplicateFragment;

class UniqueFragmentNamesRuleTest extends RuleTestCase
{
    protected function getRuleClassName(): string
    {
        return UniqueFragmentNamesRule::class;
    }

    public function testNoFragments()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              field
            }
            ')
        );
    }

    public function testOneFragment()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              ...fragA
            }
            fragment fragA on Type {
              field
            }
            ')
        );
    }

    public function testManyFragments()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              ...fragA
              ...fragB
              ...fragC
            }
            fragment fragA on Type {
              fieldA
            }
            fragment fragB on Type {
              fieldB
            }
            fragment fragC on Type {
              fieldC
            }
            ')
        );
    }

    public function testInlineFragmentsAreAlwaysUnique()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              ...on Type {
                fieldA
              }
              ...on Type {
                fieldB
              }
            }
            ')
        );
    }

    public function testFragmentsNamedTheSame()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              ...fragA
            }
            fragment fragA on Type {
              fieldA
            }
            fragment fragA on Type {
              fieldB
            }
            '),
            [duplicateFragment('fragA', [[4, 10], [7, 10]])]
        );
    }

    public function testFragmentsNamedTheSameWithoutBeingReferenced()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            fragment fragA on Type {
              fieldA
            }
            fragment fragA on Type {
              fieldB
            }
            '),
            [duplicateFragment('fragA', [[1, 10], [4, 10]])]
        );
    }
}
