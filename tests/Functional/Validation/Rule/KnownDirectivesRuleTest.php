<?php

namespace Digia\GraphQL\Test\Functional\Validation\Rule;

use Digia\GraphQL\Validation\Rule\KnownDirectivesRule;
use function Digia\GraphQL\Validation\Rule\misplacedDirectiveMessage;
use function Digia\GraphQL\Validation\Rule\unknownDirectiveMessage;

function unknownDirective($directiveName, $line, $column)
{
    return [
        'message'   => unknownDirectiveMessage($directiveName),
        // TODO: Add locations when support has been added to GraphQLError.
        'locations' => null, //[['line' => $line, 'column' => $column]],
        'path'      => null,
    ];
}

function misplacedDirective($directiveName, $location, $line, $column)
{
    return [
        'message'   => misplacedDirectiveMessage($directiveName, $location),
        // TODO: Add locations when support has been added to GraphQLError.
        'locations' => null, //[['line' => $line, 'column' => $column]],
        'path'      => null,
    ];
}

class KnownDirectivesRuleTest extends RuleTestCase
{
    public function testWithNoDirectives()
    {
        $this->expectPassesRule(
            new KnownDirectivesRule(),
            '
            query Foo {
              name
              ...Frag
            }
            fragment Frag on Dog {
              name
            }
            '
        );
    }

    public function testWithKnownDirectives()
    {
        $this->expectPassesRule(
            new KnownDirectivesRule(),
            '
            {
              dog @include(if: true) {
                name
              }
              human @skip(if: false) {
                name
              }
            }
            '
        );
    }

    public function testWithUnknownDirective()
    {
        $this->expectFailsRule(
            new KnownDirectivesRule(),
            '
            {
              dog @unknown(directive: "value") {
                name
              }
            }
            ',
            [unknownDirective('unknown', 3, 13)]
        );
    }

    public function testWithManyUnknownDirectives()
    {
        $this->expectFailsRule(
            new KnownDirectivesRule(),
            '
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
            ',
            [
                unknownDirective('unknown', 3, 13),
                unknownDirective('unknown', 6, 15),
                unknownDirective('unknown', 8, 16),
            ]
        );
    }

    public function testWithWellPlacedDirectives()
    {
        $this->expectPassesRule(
            new KnownDirectivesRule(),
            '
            query Foo @onQuery {
              name @include(if: true)
              ...Frag @include(if: true)
              skippedField @skip(if: true)
              ...SkippedFrag @skip(if: true)
            }
            mutation Bar @onMutation {
              someField
            }
            '
        );
    }

    public function testWithMisplacedDirectives()
    {
        $this->expectFailsRule(
            new KnownDirectivesRule(),
            '
            query Foo @include(if: true) {
              name @onQuery
              ...Frag @onQuery
            }
            
            mutation Bar @onQuery {
              someField
            }
            ',
            [
                misplacedDirective('include', 'QUERY', 2, 17),
                misplacedDirective('onQuery', 'FIELD', 3, 14),
                misplacedDirective('onQuery', 'FRAGMENT_SPREAD', 4, 17),
                misplacedDirective('onQuery', 'MUTATION', 7, 20),
            ]
        );
    }

    public function testWithinSchemaLanguageWithWellPlacedDirectives()
    {
        $this->expectPassesRule(
            new KnownDirectivesRule(),
            '
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
            '
        );
    }

    public function testWithinSchemaLanguageWithMisplacedDirectives()
    {
        $this->expectFailsRule(
            new KnownDirectivesRule(),
            '
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
            ',
            [
                misplacedDirective('onInterface', 'OBJECT', 2, 43),
                misplacedDirective(
                    'onInputFieldDefinition',
                    'ARGUMENT_DEFINITION',
                    3,
                    30
                ),
                misplacedDirective(
                    'onInputFieldDefinition',
                    'FIELD_DEFINITION',
                    3,
                    63
                ),
                misplacedDirective('onEnum', 'SCALAR', 6, 25),
                misplacedDirective('onObject', 'INTERFACE', 8, 31),
                misplacedDirective(
                    'onInputFieldDefinition',
                    'ARGUMENT_DEFINITION',
                    9,
                    30
                ),
                misplacedDirective(
                    'onInputFieldDefinition',
                    'FIELD_DEFINITION',
                    9,
                    63
                ),
                misplacedDirective('onEnumValue', 'UNION', 12, 23),
                misplacedDirective('onScalar', 'ENUM', 14, 21),
                misplacedDirective('onUnion', 'ENUM_VALUE', 15, 20),
                misplacedDirective('onEnum', 'INPUT_OBJECT', 18, 23),
                misplacedDirective(
                    'onArgumentDefinition',
                    'INPUT_FIELD_DEFINITION',
                    19,
                    24
                ),
                misplacedDirective('onObject', 'SCHEMA', 22, 16),
            ]
        );
    }
}
