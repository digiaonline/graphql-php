<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\ExecutableDefinitionsRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\nonExecutableDefinition;

class ExecutableDefinitionsRuleTest extends RuleTestCase
{

    public function testWithOnlyOperation()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Foo {
              dog {
                name
              }
            }
            ')
        );
    }

    public function testWithOperationAndFragment()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Foo {
              dog {
                name
                ...Frag
              }
            }
            
            fragment Frag on Dog {
              name
            }
            ')
        );
    }

    public function testWithTypeDefinition()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
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
            '),
            [
                nonExecutableDefinition('Cow', [7, 1]),
                nonExecutableDefinition('Dog', [11, 1]),
            ]
        );
    }

    public function testWithSchemaDefinition()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            schema {
              query: Query
            }
            
            type Query {
              test: String
            }
            '),
            [
                nonExecutableDefinition('schema', [1, 1]),
                nonExecutableDefinition('Query', [5, 1]),
            ]
        );
    }

    protected function getRuleClassName(): string
    {
        return ExecutableDefinitionsRule::class;
    }
}
