<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\OverlappingFieldsCanBeMergedRule;

class OverlappingFieldsCanBeMergedRuleTest extends RuleTestCase
{
    public function testUniqueFields()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment uniqueFields on Dog {
  name
  nickname
}
'
        );
    }

    public function testIdenticalFields()
    {
        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment mergeIdenticalFields on Dog {
  name
  name
}
'
        );
    }

    public function testIdenticalFieldsWithIdenticalArguments()
    {
        $this->markTestIncomplete('Test fails because we do not have support for comparing arguments.');

        $this->expectPassesRule(
            new OverlappingFieldsCanBeMergedRule(),
            '
fragment mergeIdenticalFieldsWithIdenticalArgs on Dog {
  doesKnowCommand(dogCommand: SIT)
  doesKnowCommand(dogCommand: SIT)
}
'
        );
    }
}
