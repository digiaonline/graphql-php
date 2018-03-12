<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\NoUnusedFragmentsRule;
use function Digia\GraphQL\Language\locationShorthandToArray;
use function Digia\GraphQL\Validation\unusedFragmentMessage;

function unusedFragment($fragmentName, $location)
{
    return [
        'message'   => unusedFragmentMessage($fragmentName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

class NoUnusedFragmentsRuleTest extends RuleTestCase
{
    public function testAllFragmentNamesAreUsed()
    {
        $this->expectPassesRule(
            new NoUnusedFragmentsRule(),
            '
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
'
        );
    }

    public function testContainsUnknownFragments()
    {
        $this->expectFailsRule(
            new NoUnusedFragmentsRule(),
            '
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
',
            [
                unusedFragment('Unused1', [22, 1]),
                unusedFragment('Unused2', [25, 1]),
            ]
        );
    }

    public function testContainsUnknownFragmentsWithReferenceCycle()
    {
        $this->expectFailsRule(
            new NoUnusedFragmentsRule(),
            '
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
',
            [
                unusedFragment('Unused1', [22, 1]),
                unusedFragment('Unused2', [26, 1]),
            ]
        );
    }

    public function testContainsUnknownAndUndefinedFragments()
    {
        $this->expectFailsRule(
            new NoUnusedFragmentsRule(),
            '
query Foo {
  human(id: 4) {
    ...bar
  }
}
fragment foo on Human {
  name
}
',
            [unusedFragment('foo', [7, 1])]
        );
    }
}
