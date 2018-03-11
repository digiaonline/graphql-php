<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\ExecutableDefinitionsRule;
use function Digia\GraphQL\Language\locationShorthandToArray;
use function Digia\GraphQL\Validation\nonExecutableDefinitionMessage;

function nonExecutableDefinition($definitionName, $location)
{
    return [
        'message'   => nonExecutableDefinitionMessage($definitionName),
        'locations' => [locationShorthandToArray($location)],
        'path'      => null,
    ];
}

class ExecutableDefinitionsRuleTest extends RuleTestCase
{
    public function testWithOnlyOperation()
    {
        $this->expectPassesRule(
            new ExecutableDefinitionsRule(),
            '
query Foo {
  dog {
    name
  }
}
'
        );
    }

    public function testWithOperationAndFragment()
    {
        $this->expectPassesRule(
            new ExecutableDefinitionsRule(),
            '
query Foo {
  dog {
    name
    ...Frag
  }
}

fragment Frag on Dog {
  name
}
'
        );
    }

    public function testWithTypeDefinition()
    {
        $this->expectFailsRule(
            new ExecutableDefinitionsRule(),
            '
query Foo {
  dog {
    name
  }
}

type Cow {
  name: String
}

extend type Dog {
  color: String
}
',
            [
                nonExecutableDefinition('Cow', [8, 1]),
                nonExecutableDefinition('Dog', [12, 1]),
            ]
        );
    }

    public function testWithSchemaDefinition()
    {
        $this->expectFailsRule(
            new ExecutableDefinitionsRule(),
            '
schema {
  query: Query
}

type Query {
  test: String
}
',
            [
                nonExecutableDefinition('schema', [2, 1]),
                nonExecutableDefinition('Query', [6, 1]),
            ]
        );
    }
}
