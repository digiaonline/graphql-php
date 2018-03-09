<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\FragmentsOnCompositeTypesRule;
use function Digia\GraphQL\Validation\Rule\fragmentOnNonCompositeMessage;

function fragmentOnNonComposite($fragmentName, $typeName, $line, $column)
{
    return [
        'message'   => fragmentOnNonCompositeMessage($fragmentName, $typeName),
        'locations' => [['line' => $line, 'column' => $column]],
        'path'      => null,
    ];
}

class FragmentsOnCompositeTypesRuleTest extends RuleTestCase
{
    public function testObjectIsValidFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            '
fragment validFragment on Dog {
  barks
}
'
        );
    }

    public function testInterfaceIsValidFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            '
fragment validFragment on Pet {
  name
}
'
        );
    }

    public function testObjectIsValidInlineFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            '
fragment validFragment on Pet {
  ... on Dog {
    barks
  }
}
'
        );
    }

    public function testInlineFragmentWithoutTypeIsValid()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            '
fragment validFragment on Pet {
  ... {
    name
  }
}
'
        );
    }

    public function testUnionIsValidFragmentType()
    {
        $this->expectPassesRule(
            new FragmentsOnCompositeTypesRule(),
            '
fragment validFragment on CatOrDog {
  __typename
}
'
        );
    }

    public function testScalarIsInvalidFragmentType()
    {
        $this->expectFailsRule(
            new FragmentsOnCompositeTypesRule(),
            '
fragment scalarFragment on Boolean {
  bad
}
',
            [fragmentOnNonComposite('scalarFragment', 'Boolean', 2, 28)]
        );
    }
}
