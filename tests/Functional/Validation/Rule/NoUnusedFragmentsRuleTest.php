<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use function Digia\GraphQL\Language\dedent;
use Digia\GraphQL\Validation\Rule\NoUnusedFragmentsRule;
use function Digia\GraphQL\Test\Functional\Validation\unusedFragment;

class NoUnusedFragmentsRuleTest extends RuleTestCase
{
    public function testAllFragmentNamesAreUsed()
    {
        $this->expectPassesRule(
            new NoUnusedFragmentsRule(),
            dedent('
            {
              human(id: 4) {
                ...HumanFields1
                ... on Human {
                  ...HumanFields2
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

    public function testContainsUnknownFragments()
    {
        $this->expectFailsRule(
            new NoUnusedFragmentsRule(),
            dedent('
            query Foo {
              human(id: 4) {
                ...HumanFields1
              }
            }
            query Bar {
              human(id: 4) {
                ...HumanFields2
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
            fragment Unused1 on Human {
              name
            }
            fragment Unused2 on Human {
              name
            }
            '),
            [
                unusedFragment('Unused1', [21, 1]),
                unusedFragment('Unused2', [24, 1]),
            ]
        );
    }

    public function testContainsUnknownFragmentsWithReferenceCycle()
    {
        $this->expectFailsRule(
            new NoUnusedFragmentsRule(),
            dedent('
            query Foo {
              human(id: 4) {
                ...HumanFields1
              }
            }
            query Bar {
              human(id: 4) {
                ...HumanFields2
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
            fragment Unused1 on Human {
              name
              ...Unused2
            }
            fragment Unused2 on Human {
              name
              ...Unused1
            }
            '),
            [
                unusedFragment('Unused1', [21, 1]),
                unusedFragment('Unused2', [25, 1]),
            ]
        );
    }

    public function testContainsUnknownAndUndefinedFragments()
    {
        $this->expectFailsRule(
            new NoUnusedFragmentsRule(),
            dedent('
            query Foo {
              human(id: 4) {
                ...bar
              }
            }
            fragment foo on Human {
              name
            }
            '),
            [unusedFragment('foo', [6, 1])]
        );
    }
}
