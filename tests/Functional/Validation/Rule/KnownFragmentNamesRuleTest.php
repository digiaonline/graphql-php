<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Language\dedent;
use Digia\GraphQL\Validation\Rule\KnownFragmentNamesRule;
use function Digia\GraphQL\Test\Functional\Validation\unknownFragment;

class KnownFragmentNamesRuleTest extends RuleTestCase
{
    public function testKnownFragmentNamesAreValid()
    {
        $this->expectPassesRule(
            new KnownFragmentNamesRule(),
            dedent('
            {
              human(id: 4) {
                ...HumanFields1
                ... on Human {
                  ...HumanFields2
                }
                ... {
                  name
                }
              }
            }
            fragment HumanFields1 on Human {
              name
              ...HumanFields3
            }
            fragment HumanFields2 on Human {
              name
            }
            fragment HumanFields3 on Human {
              name
            }
            ')
        );
    }

    public function testUnknownFragmentNamesAreInvalid()
    {
        $this->expectFailsRule(
            new KnownFragmentNamesRule(),
            dedent('
            {
              human(id: 4) {
                ...UnknownFragment1
                ... on Human {
                  ...UnknownFragment2
                }
              }
            }
            fragment HumanFields on Human {
              name
              ...UnknownFragment3
            }
            '),
            [
                unknownFragment('UnknownFragment1', [3, 8]),
                unknownFragment('UnknownFragment2', [5, 10]),
                unknownFragment('UnknownFragment3', [11, 6]),
            ]
        );
    }
}
