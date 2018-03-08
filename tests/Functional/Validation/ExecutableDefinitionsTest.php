<?php

namespace Digia\GraphQL\Test\Functional\Validation;

use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Language\AST\Visitor\VisitorBreak;
use Digia\GraphQL\Validation\Rule\ExecutableDefinitionRule;

class ExecutableDefinitionsTest extends RuleTestCase
{
    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testWithOnlyOperation()
    {
        $this->expectPassesRule(
            GraphQL::get(ExecutableDefinitionRule::class),
            '
            query Foo {
              dog {
                name
              }
            }
            '
        );
    }

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testWithOperationAndFragment()
    {
        $this->expectPassesRule(
            GraphQL::get(ExecutableDefinitionRule::class),
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

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testWithTypeDefinition()
    {
        $this->expectFailsRule(
            GraphQL::get(ExecutableDefinitionRule::class),
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
            '
        );
    }

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testWithSchemaDefinition()
    {
        $this->expectFailsRule(
            GraphQL::get(ExecutableDefinitionRule::class),
            '
            schema {
              query: Query
            }
            
            type Query {
              test: String
            }
            '
        );
    }
}
