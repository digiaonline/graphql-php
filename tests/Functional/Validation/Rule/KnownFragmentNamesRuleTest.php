<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\KnownFragmentNamesRule;
use function Digia\GraphQL\Language\locationShorthandToArray;
use function Digia\GraphQL\Validation\Rule\unknownFragmentMessage;

function unknownFragment($fragmentName, $location)
{
    return [
        'message'   => unknownFragmentMessage($fragmentName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

class KnownFragmentNamesRuleTest extends RuleTestCase
{
    public function testKnownFragmentNamesAreValid()
    {
        $this->expectPassesRule(
            new KnownFragmentNamesRule(),
            '
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
'
        );
    }

    public function testUnknownFragmentNamesAreInvalid()
    {
        $this->expectFailsRule(
            new KnownFragmentNamesRule(),
            '
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
',
            [
                unknownFragment('UnknownFragment1', [4, 8]),
                unknownFragment('UnknownFragment2', [6, 10]),
                unknownFragment('UnknownFragment3', [12, 6]),
            ]
        );
    }
}
