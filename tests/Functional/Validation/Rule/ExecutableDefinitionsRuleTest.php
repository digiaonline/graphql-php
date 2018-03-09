<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Language\AST\Visitor\VisitorBreak;
use Digia\GraphQL\Validation\Rule\ExecutableDefinitionRule;
use function Digia\GraphQL\Validation\Rule\nonExecutableDefinitionMessage;

/**
 * @param string $definitionName
 * @param int    $line
 * @param int    $column
 * @return array
 */
function nonExecutableDefinition(string $definitionName, int $line, int $column)
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
    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
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

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
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

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testWithTypeDefinition()
    {
        // TODO: Add expectedErrors
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

    /**
     * @throws VisitorBreak
     * @throws \Exception
     * @throws \TypeError
     */
    public function testWithSchemaDefinition()
    {
        // TODO: Add expectedErrors
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
