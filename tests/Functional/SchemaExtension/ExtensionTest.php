<?php

namespace Digia\GraphQL\Test\Functional\SchemaExtension;

use Digia\GraphQL\Error\ExtensionException;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\SchemaExtension\SchemaExtenderInterface;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\parse;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use function Digia\GraphQL\Type\GraphQLEnumType;
use function Digia\GraphQL\Type\GraphQLID;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\GraphQLUnionType;
use Digia\GraphQL\Type\SchemaInterface;
use function Digia\GraphQL\validateSchema;

class ExtensionTest extends TestCase
{
    /**
     * @var SchemaExtenderInterface
     */
    protected $extender;

    protected $testSchema;
    protected $someInterfaceType;
    protected $fooType;
    protected $barType;
    protected $bizType;
    protected $someUnionType;
    protected $someEnumType;

    public function setUp()
    {
        $this->extender = GraphQL::make(SchemaExtenderInterface::class);

        $this->someInterfaceType = GraphQLInterfaceType([
            'name'   => 'SomeInterface',
            'fields' => function () {
                return [
                    'name' => ['type' => GraphQLString()],
                    'some' => ['type' => $this->someInterfaceType],
                ];
            },
        ]);

        $this->fooType = GraphQLObjectType([
            'name'       => 'Foo',
            'interfaces' => [$this->someInterfaceType],
            'fields'     => function () {
                return [
                    'name' => ['type' => GraphQLString()],
                    'some' => ['type' => $this->someInterfaceType],
                    'tree' => ['type' => GraphQLNonNull(GraphQLList($this->fooType))],
                ];
            },
        ]);

        $this->barType = GraphQLObjectType([
            'name'       => 'Bar',
            'interfaces' => [$this->someInterfaceType],
            'fields'     => function () {
                return [
                    'name' => ['type' => GraphQLString()],
                    'some' => ['type' => $this->someInterfaceType],
                    'foo'  => ['type' => $this->fooType],
                ];
            },
        ]);

        $this->bizType = GraphQLObjectType([
            'name'   => 'Biz',
            'fields' => function () {
                return [
                    'fizz' => ['type' => GraphQLString()]
                ];
            },
        ]);

        $this->someUnionType = GraphQLUnionType([
            'name'  => 'SomeUnion',
            'types' => [$this->fooType, $this->bizType],
        ]);

        $this->someEnumType = GraphQLEnumType([
            'name'   => 'SomeEnum',
            'values' => [
                'ONE' => ['value' => 1],
                'TWO' => ['value' => 2],
            ],
        ]);

        $this->testSchema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Query',
                'fields' => function () {
                    return [
                        'foo'           => ['type' => $this->fooType],
                        'someUnion'     => ['type' => $this->someUnionType],
                        'someEnum'      => ['type' => $this->someEnumType],
                        'someInterface' => [
                            'type' => $this->someInterfaceType,
                            'args' => ['id' => ['type' => GraphQLNonNull(GraphQLID())]],
                        ],
                    ];
                },
            ]),
            'types' => [$this->fooType, $this->barType],
        ]);
    }

    // returns the original schema when there are no type definitions

    public function testReturnsTheOriginalSchemaWhenThereAreNoTypeDefinitions()
    {
        $extendedSchema = $this->extendTestSchema('{ field }');
        $this->assertEquals($this->testSchema, $extendedSchema);
    }

    // extends without altering original schema

    public function testExtendsWithoutAlteringOriginalSchema()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        extend type Query {
          newField: String
        }
        '));

        $this->assertNotEquals($this->testSchema, $extendedSchema);
        // TODO: Assert printed schemas
    }

    // can be used for limited execution

    public function testCanBeUsedForLimitedExecution()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        extend type Query {
          newField: String
        }
        '));

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = graphql($extendedSchema, '{ newField }', ['newField' => 123]);

        $this->assertEquals(['newField' => '123'], $result['data']);
    }

    // can describe the extended fields

    public function testCanDescribeTheExtendedFields()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        extend type Query {
          "New field description."
          newField: String
        }
        '));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(
            'New field description.',
            $extendedSchema->getQueryType()->getField('newField')->getDescription()
        );
    }

    // Skip: can describe the extended fields with legacy comments

    // TODO: describes extended fields with strings when present

    // correctly assign AST nodes to new and extended types

    public function testCorrectlyAssignASTNodesToNewAndExtendedTypes()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        extend type Query {
          newField(testArg: TestInput): TestEnum
        }
        
        enum TestEnum {
          TEST_VALUE
        }
        
        input TestInput {
          testInputField: TestEnum
        }
        '));

        /** @noinspection PhpUnhandledExceptionInspection */
        $secondExtensionAST = parse(dedent('
        extend type Query {
          oneMoreNewField: TestUnion
        }
        
        union TestUnion = TestType
        
        interface TestInterface {
          interfaceField: String
        }
        
        type TestType implements TestInterface {
          interfaceField: String
        }
        
        directive @test(arg: Int) on FIELD
        '));

        $extendedTwiceSchema = $this->extendSchema(
            $extendedSchema,
            $secondExtensionAST
        );

        $query = $extendedTwiceSchema->getQueryType();
        $testInput = $extendedTwiceSchema->getType('TestInput');
        $testEnum = $extendedTwiceSchema->getType('TestEnum');
        $testUnion = $extendedTwiceSchema->getType('TestUnion');
        $testInterface = $extendedTwiceSchema->getType('TestInterface');
        $testType = $extendedTwiceSchema->getType('TestType');
        $testDirective = $extendedTwiceSchema->getDirective('test');

        $this->assertCount(2, $query->getExtensionAstNodes());
        $this->assertCount(0, $testType->getExtensionAstNodes());

        // TODO: Assert printed types
    }

    // builds types with deprecated fields/values

    public function gestBuildTypesWithDeprecatedFieldsOrValues()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        type TypeWithDeprecatedField {
          newDeprecatedField: String @deprecated(reason: "not used anymore")
        }
        
        enum EnumWithDeprecatedValue {
          DEPRECATED @deprecated(reason: "do not use")
        }
        '));

        /** @var Field $deprecatedFieldDefinition */
        /** @noinspection PhpUndefinedMethodInspection */
        $deprecatedFieldDefinition = $extendedSchema
            ->getType('TypeWithDeprecatedField')
            ->getField('newDeprecatedField');

        $this->assertTrue($deprecatedFieldDefinition->getIsDeprecated());
        $this->assertEquals('not used anymore', $deprecatedFieldDefinition->getDeprecationReason());

        /** @var EnumValue $deprecatedEnumDefinition */
        /** @noinspection PhpUndefinedMethodInspection */
        $deprecatedEnumDefinition = $extendedSchema
            ->getType('EnumWithDeprecatedValue')
            ->getValue('DEPRECATED');

        $this->assertTrue($deprecatedEnumDefinition->getIsDeprecated());
        $this->assertEquals('do not use', $deprecatedEnumDefinition->getDeprecationReason());
    }

    // extends objects with deprecated fields

    public function testExtendsObjectsWithDeprecatedFields()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        extend type Foo {
          deprecatedField: String @deprecated(reason: "not used anymore")
        }
        '));

        /** @var Field $deprecatedFieldDefinition */
        /** @noinspection PhpUndefinedMethodInspection */
        $deprecatedFieldDefinition = $extendedSchema
            ->getType('Foo')
            ->getField('deprecatedField');

        $this->assertTrue($deprecatedFieldDefinition->getIsDeprecated());
        $this->assertEquals('not used anymore', $deprecatedFieldDefinition->getDeprecationReason());
    }

    // extends objects by adding new unused types

    public function testExtendsObjectsByAddingNewUnusedTypes()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        type Unused {
          someField: String
        }
        '));

        $this->assertNotEquals($this->testSchema, $extendedSchema);
        // TODO: Assert printed schemas
    }

    // TODO: extends objects by adding new fields with arguments

    // TODO: extends objects by adding new fields with existing types

    // TODO: extends objects by adding implemented interfaces

    // TODO: extends objects by including new types

    // TODO: extends objects by adding implemented new interfaces

    // TODO: extends objects multiple times

    // TODO: extends interfaces by adding new fields

    // allows extension of interface with missing Object fields

    public function testAllowsExtensionOfInterfaceWithMissingObjectFields()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        extend interface SomeInterface {
          newField: String
        }
        '));

        $errors = validateSchema($extendedSchema);
        $this->assertNotEmpty($errors);

        // TODO: Assert printed schemas
    }

    // TODO: extends interfaces multiple times
    
    // may extend mutations and subscriptions

    public function testMayExtendMutationsAndSubscriptions()
    {
        $mutationSchema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name' => 'Query',
                'fields' => function() {
                    return [
                        'queryField' => ['type' => GraphQLString()],
                    ];
                },
            ]),
            'mutation' => GraphQLObjectType([
                'name' => 'Mutation',
                'fields' => function() {
                    return [
                        'mutationField' => ['type' => GraphQLString()],
                    ];
                },
            ]),
            'subscription' => GraphQLObjectType([
                'name' => 'Subscription',
                'fields' => function() {
                    return [
                        'subscriptionField' => ['type' => GraphQLString()],
                    ];
                },
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $document = parse(dedent('
        extend type Query {
          newQueryField: Int
        }
        
        extend type Mutation {
          newMutationField: Int
        }
        
        extend type Subscription {
          newSubscriptionField: Int
        }
        '));

        $extendedSchema = $this->extendSchema($mutationSchema, $document);
        $this->assertNotEquals($mutationSchema, $extendedSchema);

        // TODO: Assert printed schemas
    }

    // may extend directives with new simple directive

    public function testMayExtendDirectivesWithNewSimpleDirective()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        directive @neat on QUERY
        '));

        $extendedDirective = $extendedSchema->getDirective('neat');
        $this->assertEquals('neat', $extendedDirective->getName());
        $this->assertContains('QUERY', $extendedDirective->getLocations());
    }

    // sets correct description when extending with a new directive

    public function testSetsCorrectDescriptionWhenExtendingWithANewDirective()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        """
        new directive
        """
        directive @new on QUERY
        '));

        $extendedDirective = $extendedSchema->getDirective('new');
        $this->assertEquals('new directive', $extendedDirective->getDescription());
    }

    // Skip: sets correct description using legacy comments

    // may extend directives with new complex directive

    public function testMayExtendDirectivesWithNewComplexDirective()
    {
        $extendedSchema = $this->extendTestSchema(dedent('
        directive @profile(enable: Boolean! tag: String) on QUERY | FIELD
        '));

        $extendedDirective = $extendedSchema->getDirective('profile');
        $this->assertContains('QUERY', $extendedDirective->getLocations());
        $this->assertContains('FIELD', $extendedDirective->getLocations());

        $arguments = $extendedDirective->getArguments();
        $argument0 = $arguments[0];
        $argument1 = $arguments[1];

        $this->assertCount(2, $arguments);

        $this->assertEquals('enable', $argument0->getName());
        $this->assertInstanceOf(NonNullType::class, $argument0->getType());
        $this->assertInstanceOf(ScalarType::class, $argument0->getType()->getOfType());

        $this->assertEquals('tag', $argument1->getName());
        $this->assertInstanceOf(ScalarType::class, $argument1->getType());
    }

    // does not allow replacing a default directive

    public function testDoesNotAllowReplacingADefaultDirective()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $document = parse(dedent('
        directive @include(if: Boolean!) on FIELD | FRAGMENT_SPREAD
        '));

        $this->expectException(ExtensionException::class);
        $this->expectExceptionMessage('Directive "include" already exists in the schema. It cannot be redefined.');
        $this->extendTestSchema($document);
    }

    protected function extendSchema($schema, $document)
    {
        return $this->extender->extend($schema, $document);
    }

    protected function extendTestSchema($sdl)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $document       = parse($sdl);
        $extendedSchema = $this->extendSchema($this->testSchema, $document);
        // TODO: Assert printed schemas
        return $extendedSchema;
    }
}
