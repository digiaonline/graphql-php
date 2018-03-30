<?php

namespace Digia\GraphQL\Test\Functional\Language;

use Digia\GraphQL\Error\SyntaxErrorException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\TypeNameEnum;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Test\jsonEncode;

class SchemaParserTest extends TestCase
{

    public function testSimpleType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
type Hello {
  world: String
}');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 6, 'end' => 11]),
                    'interfaces'  => [],
                    'directives'  => [],
                    'fields'      => [
                        $this->fieldNode(
                            $this->nameNode('world', ['start' => 16, 'end' => 21]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 23, 'end' => 29]),
                            ['start' => 16, 'end' => 29]
                        ),
                    ],
                    'loc'         => ['start' => 1, 'end' => 31],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 31],
        ]), $node->toJSON());
    }

    public function testParsesWithDescriptionString()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
"Description"
type Hello {
  world: String
}');

        $this->assertArraySubset([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'name'        => $this->nameNode('Hello', ['start' => 20, 'end' => 25]),
                    'description' => [
                        'kind'  => NodeKindEnum::STRING,
                        'value' => 'Description',
                        'loc'   => ['start' => 1, 'end' => 14],
                    ],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 45],
        ], $node->toArray());
    }

    public function testParsesWithDescriptionMultiLineString()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
"""
Description
"""
# Even with comments between them
type Hello {
  world: String
}');

        $this->assertArraySubset([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'name'        => $this->nameNode('Hello', ['start' => 60, 'end' => 65]),
                    'description' => [
                        'kind'  => NodeKindEnum::STRING,
                        'value' => 'Description',
                        'loc'   => ['start' => 1, 'end' => 20],
                    ],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 85],
        ], $node->toArray());
    }

    public function testSimpleExtension()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
extend type Hello {
  world: String
}');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'       => NodeKindEnum::OBJECT_TYPE_EXTENSION,
                    'name'       => $this->nameNode('Hello', ['start' => 13, 'end' => 18]),
                    'interfaces' => [],
                    'directives' => [],
                    'fields'     => [
                        $this->fieldNode(
                            $this->nameNode('world', ['start' => 23, 'end' => 28]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 30, 'end' => 36]),
                            ['start' => 23, 'end' => 36]
                        ),
                    ],
                    'loc'        => ['start' => 1, 'end' => 38],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 38],
        ]), $node->toJSON());
    }

    public function testExtensionWithoutFields()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('extend type Hello implements Greeting');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'       => NodeKindEnum::OBJECT_TYPE_EXTENSION,
                    'name'       => $this->nameNode('Hello', ['start' => 12, 'end' => 17]),
                    'interfaces' => [
                        $this->typeNode('Greeting', ['start' => 29, 'end' => 37]),
                    ],
                    'directives' => [],
                    'fields'     => [],
                    'loc'        => ['start' => 0, 'end' => 37],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 37],
        ]), $node->toJSON());
    }

    public function testExtensionWithoutFieldsFollowedByExtension()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
      extend type Hello implements Greeting

      extend type Hello implements SecondGreeting
    ');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'       => NodeKindEnum::OBJECT_TYPE_EXTENSION,
                    'name'       => $this->nameNode('Hello', ['start' => 19, 'end' => 24]),
                    'interfaces' => [
                        $this->typeNode('Greeting', ['start' => 36, 'end' => 44]),
                    ],
                    'directives' => [],
                    'fields'     => [],
                    'loc'        => ['start' => 7, 'end' => 44],
                ],
                [
                    'kind'       => NodeKindEnum::OBJECT_TYPE_EXTENSION,
                    'name'       => $this->nameNode('Hello', ['start' => 64, 'end' => 69]),
                    'interfaces' => [
                        $this->typeNode('SecondGreeting', ['start' => 81, 'end' => 95]),
                    ],
                    'directives' => [],
                    'fields'     => [],
                    'loc'        => ['start' => 52, 'end' => 95],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 100],
        ]), $node->toJSON());
    }

    public function testExtensionWithoutAnythingThrows()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Unexpected <EOF>');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('extend type Hello');
    }

    public function testExtensionsDoNotIncludeDescriptions()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Unexpected Name "extend"');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('
"Description"
extend type Hello {
  world: String
}');

        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Unexpected String "Description"');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('
extend "Description" type Hello {
  world: String
}');
    }

    public function testSimpleNonNullType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
type Hello {
  world: String!
}');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 6, 'end' => 11]),
                    'interfaces'  => [],
                    'directives'  => [],
                    'fields'      => [
                        $this->fieldNode(
                            $this->nameNode('world', ['start' => 16, 'end' => 21]),
                            [
                                'kind' => NodeKindEnum::NON_NULL_TYPE,
                                'type' => $this->typeNode(TypeNameEnum::STRING, ['start' => 23, 'end' => 29]),
                                'loc'  => ['start' => 23, 'end' => 30],
                            ],
                            ['start' => 16, 'end' => 30]
                        ),
                    ],
                    'loc'         => ['start' => 1, 'end' => 32],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 32],
        ]), $node->toJSON());
    }

    public function testSimpleTypeInheritingInterface()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('type Hello implements World { field: String }');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 5, 'end' => 10]),
                    'interfaces'  => [
                        $this->typeNode('World', ['start' => 22, 'end' => 27]),
                    ],
                    'directives'  => [],
                    'fields'      => [
                        $this->fieldNode(
                            $this->nameNode('field', ['start' => 30, 'end' => 35]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 37, 'end' => 43]),
                            ['start' => 30, 'end' => 43]
                        ),
                    ],
                    'loc'         => ['start' => 0, 'end' => 45],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 45],
        ]), $node->toJSON());
    }

    public function testSimpleTypeInheritingMultipleInterface()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('type Hello implements Wo & rld { field: String }');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 5, 'end' => 10]),
                    'interfaces'  => [
                        $this->typeNode('Wo', ['start' => 22, 'end' => 24]),
                        $this->typeNode('rld', ['start' => 27, 'end' => 30]),
                    ],
                    'directives'  => [],
                    'fields'      => [
                        $this->fieldNode(
                            $this->nameNode('field', ['start' => 33, 'end' => 38]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 40, 'end' => 46]),
                            ['start' => 33, 'end' => 46]
                        ),
                    ],
                    'loc'         => ['start' => 0, 'end' => 48],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 48],
        ]), $node->toJSON());
    }

    public function testSimpleTypeInheritingMultipleInterfaceWithLeadingAmpersand()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('type Hello implements & Wo & rld { field: String }');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 5, 'end' => 10]),
                    'interfaces'  => [
                        $this->typeNode('Wo', ['start' => 24, 'end' => 26]),
                        $this->typeNode('rld', ['start' => 29, 'end' => 32]),
                    ],
                    'directives'  => [],
                    'fields'      => [
                        $this->fieldNode(
                            $this->nameNode('field', ['start' => 35, 'end' => 40]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 42, 'end' => 48]),
                            ['start' => 35, 'end' => 48]
                        ),
                    ],
                    'loc'         => ['start' => 0, 'end' => 50],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 50],
        ]), $node->toJSON());
    }

    public function testSingleValueEnum()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('enum Hello { WORLD }');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::ENUM_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 5, 'end' => 10]),
                    'directives'  => [],
                    'values'      => [
                        $this->enumValueNode('WORLD', ['start' => 13, 'end' => 18]),
                    ],
                    'loc'         => ['start' => 0, 'end' => 20],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 20],
        ]), $node->toJSON());
    }

    public function testDoubleValueEnum()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('enum Hello { WO, RLD }');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::ENUM_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 5, 'end' => 10]),
                    'directives'  => [],
                    'values'      => [
                        $this->enumValueNode('WO', ['start' => 13, 'end' => 15]),
                        $this->enumValueNode('RLD', ['start' => 17, 'end' => 20]),
                    ],
                    'loc'         => ['start' => 0, 'end' => 22],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 22],
        ]), $node->toJSON());
    }

    public function testSimpleInterface()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
interface Hello {
  world: String
}');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::INTERFACE_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 11, 'end' => 16]),
                    'directives'  => [],
                    'fields'      => [
                        $this->fieldNode(
                            $this->nameNode('world', ['start' => 21, 'end' => 26]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 28, 'end' => 34]),
                            ['start' => 21, 'end' => 34]
                        ),
                    ],
                    'loc'         => ['start' => 1, 'end' => 36],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 36],
        ]), $node->toJSON());
    }

    public function parseSimpleFieldWithArgument()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
type Hello {
  world(flag: Boolean): String
}');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 6, 'end' => 11]),
                    'interfaces'  => [],
                    'directives'  => [],
                    'fields'      => [
                        $this->fieldNodeWithArguments(
                            $this->nameNode('world', ['start' => 16, 'end' => 21]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 45, 'end' => 51]),
                            [
                                $this->inputValueNode(
                                    $this->nameNode('flag', ['start' => 22, 'end' => 26]),
                                    $this->typeNode(TypeNameEnum::BOOLEAN, ['start' => 28, 'end' => 35]),
                                    null,
                                    ['start' => 22, 'end' => 35]
                                ),
                            ],
                            ['start' => 16, 'end' => 44]
                        ),
                    ],
                    'loc'         => ['start' => 1, 'end' => 46],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 46],
        ]), $node->toJSON());
    }

    public function testSimpleFieldWithArgumentWithDefaultValue()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
type Hello {
  world(flag: Boolean = true): String
}');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 6, 'end' => 11]),
                    'interfaces'  => [],
                    'directives'  => [],
                    'fields'      => [
                        $this->fieldNodeWithArguments(
                            $this->nameNode('world', ['start' => 16, 'end' => 21]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 45, 'end' => 51]),
                            [
                                $this->inputValueNode(
                                    $this->nameNode('flag', ['start' => 22, 'end' => 26]),
                                    $this->typeNode(TypeNameEnum::BOOLEAN, ['start' => 28, 'end' => 35]),
                                    [
                                        'kind'  => NodeKindEnum::BOOLEAN,
                                        'value' => true,
                                        'loc'   => ['start' => 38, 'end' => 42],
                                    ],
                                    ['start' => 22, 'end' => 42]
                                ),
                            ],
                            ['start' => 16, 'end' => 51]
                        ),
                    ],
                    'loc'         => ['start' => 1, 'end' => 53],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 53],
        ]), $node->toJSON());
    }

    public function testSimpleFieldWithListArgument()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
type Hello {
  world(things: [String]): String
}');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 6, 'end' => 11]),
                    'interfaces'  => [],
                    'directives'  => [],
                    'fields'      => [
                        $this->fieldNodeWithArguments(
                            $this->nameNode('world', ['start' => 16, 'end' => 21]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 41, 'end' => 47]),
                            [
                                $this->inputValueNode(
                                    $this->nameNode('things', ['start' => 22, 'end' => 28]),
                                    [
                                        'kind' => NodeKindEnum::LIST_TYPE,
                                        'type' => $this->typeNode(TypeNameEnum::STRING, ['start' => 31, 'end' => 37]),
                                        'loc'  => ['start' => 30, 'end' => 38],
                                    ],
                                    null,
                                    ['start' => 22, 'end' => 38]
                                ),
                            ],
                            ['start' => 16, 'end' => 47]
                        ),
                    ],
                    'loc'         => ['start' => 1, 'end' => 49],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 49],
        ]), $node->toJSON());
    }

    public function parseSimpleFieldWithTwoArguments()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
type Hello {
  world(argOne: Boolean, argTwo: Int): String
}');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::OBJECT_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 6, 'end' => 11]),
                    'interfaces'  => [],
                    'directives'  => [],
                    'fields'      => [
                        $this->fieldNodeWithArguments(
                            $this->nameNode('world', ['start' => 16, 'end' => 21]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 53, 'end' => 59]),
                            [
                                $this->inputValueNode(
                                    $this->nameNode('argOne', ['start' => 22, 'end' => 28]),
                                    $this->typeNode(TypeNameEnum::BOOLEAN, ['start' => 30, 'end' => 37]),
                                    null,
                                    ['start' => 22, 'end' => 37]
                                ),
                                $this->inputValueNode(
                                    $this->nameNode('argTwo', ['start' => 39, 'end' => 45]),
                                    $this->typeNode(TypeNameEnum::INT, ['start' => 47, 'end' => 50]),
                                    null,
                                    ['start' => 39, 'end' => 50]
                                ),
                            ],
                            ['start' => 16, 'end' => 59]
                        ),
                    ],
                    'loc'         => ['start' => 1, 'end' => 61],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 61],
        ]), $node->toJSON());
    }

    public function testSimpleUnion()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('union Hello = World');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::UNION_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 6, 'end' => 11]),
                    'directives'  => [],
                    'types'       => [
                        $this->typeNode('World', ['start' => 14, 'end' => 19]),
                    ],
                    'loc'         => ['start' => 0, 'end' => 19],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 19],
        ]), $node->toJSON());
    }

    public function testSimpleUnionWithTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('union Hello = Wo | Rld');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::UNION_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 6, 'end' => 11]),
                    'directives'  => [],
                    'types'       => [
                        $this->typeNode('Wo', ['start' => 14, 'end' => 16]),
                        $this->typeNode('Rld', ['start' => 19, 'end' => 22]),
                    ],
                    'loc'         => ['start' => 0, 'end' => 22],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 22],
        ]), $node->toJSON());
    }

    public function testSimpleUnionWithTypesAndLeadingPipe()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('union Hello = | Wo | Rld');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::UNION_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 6, 'end' => 11]),
                    'directives'  => [],
                    'types'       => [
                        $this->typeNode('Wo', ['start' => 16, 'end' => 18]),
                        $this->typeNode('Rld', ['start' => 21, 'end' => 24]),
                    ],
                    'loc'         => ['start' => 0, 'end' => 24],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 24],
        ]), $node->toJSON());
    }

    public function testUnionFailsWithNoTypes()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Expected Name, found <EOF>');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('union Hello = |');
    }

    public function testUnionFailsWithLeadingDoublePipe()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Expected Name, found |');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('union Hello = || Wo | Rld');
    }

    public function testUnionFailsWithTrailingPipe()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Expected Name, found <EOF>');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('union Hello = | Wo | Rld |');
    }

    public function testSimpleScalar()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('scalar Hello');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::SCALAR_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 7, 'end' => 12]),
                    'directives'  => [],
                    'loc'         => ['start' => 0, 'end' => 12],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 12],
        ]), $node->toJSON());
    }

    public function testSimpleInputObject()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
input Hello {
  world: String
}');

        $this->assertEquals(jsonEncode([
            'kind'        => NodeKindEnum::DOCUMENT,
            'definitions' => [
                [
                    'kind'        => NodeKindEnum::INPUT_OBJECT_TYPE_DEFINITION,
                    'description' => null,
                    'name'        => $this->nameNode('Hello', ['start' => 7, 'end' => 12]),
                    'directives'  => [],
                    'fields'      => [
                        $this->inputValueNode(
                            $this->nameNode('world', ['start' => 17, 'end' => 22]),
                            $this->typeNode(TypeNameEnum::STRING, ['start' => 24, 'end' => 30]),
                            null,
                            ['start' => 17, 'end' => 30]
                        ),
                    ],
                    'loc'         => ['start' => 1, 'end' => 32],
                ],
            ],
            'loc'         => ['start' => 0, 'end' => 32],
        ]), $node->toJSON());
    }

    public function testSimpleInputObjectWithArgumentsShouldFail()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Expected :, found (');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('
input Hello {
  world(foo: Int): String
}');
    }

    public function testDirectiveWithIncorrectLocations()
    {
        $this->expectException(SyntaxErrorException::class);
        $this->expectExceptionMessage('Unexpected Name "INCORRECT_LOCATION"');
        /** @noinspection PhpUnhandledExceptionInspection */
        parse('
directive @foo on FIELD | INCORRECT_LOCATION');
    }

    protected function typeNode($name, $loc)
    {
        return [
            'kind' => NodeKindEnum::NAMED_TYPE,
            'name' => $this->nameNode($name, $loc),
            'loc'  => $loc,
        ];
    }

    protected function nameNode($name, $loc)
    {
        return [
            'kind'  => NodeKindEnum::NAME,
            'value' => $name,
            'loc'   => $loc,
        ];
    }

    protected function fieldNode($name, $type, $loc)
    {
        return $this->fieldNodeWithArguments($name, $type, [], $loc);
    }

    protected function fieldNodeWithArguments($name, $type, $arguments, $loc)
    {
        return [
            'kind'        => NodeKindEnum::FIELD_DEFINITION,
            'description' => null,
            'name'        => $name,
            'arguments'   => $arguments,
            'type'        => $type,
            'directives'  => [],
            'loc'         => $loc,
        ];
    }

    protected function enumValueNode($name, $loc)
    {
        return [
            'kind'        => NodeKindEnum::ENUM_VALUE_DEFINITION,
            'description' => null,
            'name'        => $this->nameNode($name, $loc),
            'directives'  => [],
            'loc'         => $loc,
        ];
    }

    protected function inputValueNode($name, $type, $defaultValue, $loc)
    {
        return [
            'kind'         => NodeKindEnum::INPUT_VALUE_DEFINITION,
            'description'  => null,
            'name'         => $name,
            'type'         => $type,
            'defaultValue' => $defaultValue,
            'directives'   => [],
            'loc'          => $loc,
        ];
    }
}
