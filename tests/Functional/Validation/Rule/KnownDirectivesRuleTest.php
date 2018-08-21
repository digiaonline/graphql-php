<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\KnownDirectivesRule;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Test\Functional\Validation\misplacedDirective;
use function Digia\GraphQL\Test\Functional\Validation\unknownDirective;

class KnownDirectivesRuleTest extends RuleTestCase
{
    protected function getRuleClassName(): string
    {
        return KnownDirectivesRule::class;
    }

    public function testWithNoDirectives()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Foo {
              name
              ...Frag
            }
            fragment Frag on Dog {
              name
            }
            ')
        );
    }

    public function testWithKnownDirectives()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            {
              dog @include(if: true) {
                name
              }
              human @skip(if: false) {
                name
              }
            }
            ')
        );
    }

    public function testWithUnknownDirective()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              dog @unknown(directive: "value") {
                name
              }
            }
            '),
            [unknownDirective('unknown', [2, 7])]
        );
    }

    public function testWithManyUnknownDirectives()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            {
              dog @unknown(directive: "value") {
                name
              }
              human @unknown(directive: "value") {
                name
                pets @unknown(directive: "value") {
                  name
                }
              }
            }
            '),
            [
                unknownDirective('unknown', [2, 7]),
                unknownDirective('unknown', [5, 9]),
                unknownDirective('unknown', [7, 10]),
            ]
        );
    }

    public function testWithWellPlacedDirectives()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            query Foo @onQuery {
              name @include(if: true)
              ...Frag @include(if: true)
              skippedField @skip(if: true)
              ...SkippedFrag @skip(if: true)
            }
            mutation Bar @onMutation {
              someField
            }
            ')
        );
    }

    public function testWithMisplacedDirectives()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            query Foo @include(if: true) {
              name @onQuery
              ...Frag @onQuery
            }
            
            mutation Bar @onQuery {
              someField
            }
            '),
            [
                misplacedDirective('include', 'QUERY', [1, 11]),
                misplacedDirective('onQuery', 'FIELD', [2, 8]),
                misplacedDirective('onQuery', 'FRAGMENT_SPREAD', [3, 11]),
                misplacedDirective('onQuery', 'MUTATION', [6, 14]),
            ]
        );
    }

    public function testWithinSchemaLanguageWithWellPlacedDirectives()
    {
        $this->expectPassesRule(
            $this->rule,
            dedent('
            type MyObj implements MyInterface @onObject {
              myField(myArg: Int @onArgumentDefinition): String @onFieldDefinition
            }
            
            extend type MyObj @onObject
            
            scalar MyScalar @onScalar
            
            extend scalar MyScalar @onScalar
            
            interface MyInterface @onInterface {
              myField(myArg: Int @onArgumentDefinition): String @onFieldDefinition
            }
            
            extend interface MyInterface @onInterface
            
            union MyUnion @onUnion = MyObj | Other
            
            extend union MyUnion @onUnion
            
            enum MyEnum @onEnum {
              MY_VALUE @onEnumValue
            }
            
            extend enum MyEnum @onEnum
            
            input MyInput @onInputObject {
              myField: Int @onInputFieldDefinition
            }
            
            extend input MyInput @onInputObject
            
            schema @onSchema {
              query: MyQuery
            }
            
            extend schema @onSchema
            ')
        );
    }

    public function testWithinSchemaLanguageWithMisplacedDirectives()
    {
        $this->expectFailsRule(
            $this->rule,
            dedent('
            type MyObj implements MyInterface @onInterface {
              myField(myArg: Int @onInputFieldDefinition): String @onInputFieldDefinition
            }
            
            scalar MyScalar @onEnum
            
            interface MyInterface @onObject {
              myField(myArg: Int @onInputFieldDefinition): String @onInputFieldDefinition
            }
            
            union MyUnion @onEnumValue = MyObj | Other
            
            enum MyEnum @onScalar {
              MY_VALUE @onUnion
            }
            
            input MyInput @onEnum {
              myField: Int @onArgumentDefinition
            }
            
            schema @onObject {
              query: MyQuery
            }
            
            extend schema @onObject
            '),
            [
                misplacedDirective('onInterface', 'OBJECT', [1, 35]),
                misplacedDirective(
                    'onInputFieldDefinition',
                    'ARGUMENT_DEFINITION',
                    [2, 22]
                ),
                misplacedDirective(
                    'onInputFieldDefinition',
                    'FIELD_DEFINITION',
                    [2, 55]
                ),
                misplacedDirective('onEnum', 'SCALAR', [5, 17]),
                misplacedDirective('onObject', 'INTERFACE', [7, 23]),
                misplacedDirective(
                    'onInputFieldDefinition',
                    'ARGUMENT_DEFINITION',
                    [8, 22]
                ),
                misplacedDirective(
                    'onInputFieldDefinition',
                    'FIELD_DEFINITION',
                    [8, 55]
                ),
                misplacedDirective('onEnumValue', 'UNION', [11, 15]),
                misplacedDirective('onScalar', 'ENUM', [13, 13]),
                misplacedDirective('onUnion', 'ENUM_VALUE', [14, 12]),
                misplacedDirective('onEnum', 'INPUT_OBJECT', [17, 15]),
                misplacedDirective(
                    'onArgumentDefinition',
                    'INPUT_FIELD_DEFINITION',
                    [18, 16]
                ),
                misplacedDirective('onObject', 'SCHEMA', [21, 8]),
                misplacedDirective('onObject', 'SCHEMA', [25, 15]),
            ]
        );
    }
}
