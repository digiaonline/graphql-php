<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\duplicateFragment;
use Digia\GraphQL\Validation\Rule\UniqueFragmentNamesRule;

class UniqueFragmentNamesRuleTest extends RuleTestCase
{
    public function testNoFragments()
    {
        $this->expectPassesRule(
            new UniqueFragmentNamesRule(),
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
            new UniqueFragmentNamesRule(),
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
            new UniqueFragmentNamesRule(),
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
            new UniqueFragmentNamesRule(),
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
            new UniqueFragmentNamesRule(),
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
            new UniqueFragmentNamesRule(),
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
