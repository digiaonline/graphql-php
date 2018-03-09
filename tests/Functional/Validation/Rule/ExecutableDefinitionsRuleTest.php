<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\ExecutableDefinitionRule;
use function Digia\GraphQL\Validation\Rule\nonExecutableDefinitionMessage;

function nonExecutableDefinition($definitionName, $line, $column)
{
    return [
        'message'   => nonExecutableDefinitionMessage($definitionName),
        // TODO: Add locations when support has been added to GraphQLError.
        'locations' => null, //[['line' => $line, 'column' => $column]],
        'path'      => null,
    ];
}

class ExecutableDefinitionsRuleTest extends RuleTestCase
{
    public function testWithOnlyOperation()
    {
        $this->expectPassesRule(
            new ExecutableDefinitionRule(),
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
            new ExecutableDefinitionRule(),
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
            new ExecutableDefinitionRule(),
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
                nonExecutableDefinition('Cow', 8, 7),
                nonExecutableDefinition('Dog', 12, 7),
            ]
        );
    }

    public function testWithSchemaDefinition()
    {
        $this->expectFailsRule(
            new ExecutableDefinitionRule(),
            '
            schema {
              query: Query
            }
            
            type Query {
              test: String
            }
            ',
            [
                nonExecutableDefinition('schema', 2, 7),
                nonExecutableDefinition('Query', 6, 7),
            ]
        );
    }
}
