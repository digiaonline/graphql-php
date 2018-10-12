<?php

namespace Digia\GraphQL\Test\Functional\Schema;

use Digia\GraphQL\Schema\Extension\SchemaExtensionException;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Schema\Extension\SchemaExtenderInterface;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\Field;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Type\Definition\ScalarType;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\idType;
use function Digia\GraphQL\Type\stringType;
use function Digia\GraphQL\Type\newEnumType;
use function Digia\GraphQL\Type\newInterfaceType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\newUnionType;
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

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->someInterfaceType = newInterfaceType([
            'name'   => 'SomeInterface',
            'fields' => function () {
                return [
                    'name' => ['type' => stringType()],
                    'some' => ['type' => $this->someInterfaceType],
                ];
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->fooType = newObjectType([
            'name'       => 'Foo',
            'interfaces' => [$this->someInterfaceType],
            'fields'     => function () {
                return [
                    'name' => ['type' => stringType()],
                    'some' => ['type' => $this->someInterfaceType],
                    'tree' => ['type' => newNonNull(newList($this->fooType))],
                ];
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->barType = newObjectType([
            'name'       => 'Bar',
            'interfaces' => [$this->someInterfaceType],
            'fields'     => function () {
                return [
                    'name' => ['type' => stringType()],
                    'some' => ['type' => $this->someInterfaceType],
                    'foo'  => ['type' => $this->fooType],
                ];
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->bizType = newObjectType([
            'name'   => 'Biz',
            'fields' => function () {
                return [
                    'fizz' => ['type' => stringType()]
                ];
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->someUnionType = newUnionType([
            'name'  => 'SomeUnion',
            'types' => [$this->fooType, $this->bizType],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->someEnumType = newEnumType([
            'name'   => 'SomeEnum',
            'values' => [
                'ONE' => ['value' => 1],
                'TWO' => ['value' => 2],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->testSchema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => function () {
                    return [
                        'foo'           => ['type' => $this->fooType],
                        'someUnion'     => ['type' => $this->someUnionType],
                        'someEnum'      => ['type' => $this->someEnumType],
                        'someInterface' => [
                            'type' => $this->someInterfaceType,
                            'args' => ['id' => ['type' => newNonNull(idType())]],
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

        $query         = $extendedTwiceSchema->getQueryType();
        $testInput     = $extendedTwiceSchema->getType('TestInput');
        $testEnum      = $extendedTwiceSchema->getType('TestEnum');
        $testUnion     = $extendedTwiceSchema->getType('TestUnion');
        $testInterface = $extendedTwiceSchema->getType('TestInterface');
        $testType      = $extendedTwiceSchema->getType('TestType');
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

        $this->assertTrue($deprecatedFieldDefinition->isDeprecated());
        $this->assertEquals('not used anymore', $deprecatedFieldDefinition->getDeprecationReason());

        /** @var EnumValue $deprecatedEnumDefinition */
        /** @noinspection PhpUndefinedMethodInspection */
        $deprecatedEnumDefinition = $extendedSchema
            ->getType('EnumWithDeprecatedValue')
            ->getValue('DEPRECATED');

        $this->assertTrue($deprecatedEnumDefinition->isDeprecated());
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

        $this->assertTrue($deprecatedFieldDefinition->isDeprecated());
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
        /** @noinspection PhpUnhandledExceptionInspection */
        $mutationSchema = newSchema([
            'query'        => newObjectType([
                'name'   => 'Query',
                'fields' => function () {
                    return [
                        'queryField' => ['type' => stringType()],
                    ];
                },
            ]),
            'mutation'     => newObjectType([
                'name'   => 'Mutation',
                'fields' => function () {
                    return [
                        'mutationField' => ['type' => stringType()],
                    ];
                },
            ]),
            'subscription' => newObjectType([
                'name'   => 'Subscription',
                'fields' => function () {
                    return [
                        'subscriptionField' => ['type' => stringType()],
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

        /** @noinspection PhpUnhandledExceptionInspection */
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
        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage('Directive "include" already exists in the schema. It cannot be redefined.');
        $this->extendTestSchema('directive @include(if: Boolean!) on FIELD | FRAGMENT_SPREAD');
    }

    // does not allow replacing a custom directive

    public function testDoesNotAllowReplacingACustomDirective()
    {
        $extendedSchema = $this->extendTestSchema('directive @meow(if: Boolean!) on FIELD | FRAGMENT_SPREAD');

        /** @noinspection PhpUnhandledExceptionInspection */
        $sdl = parse('directive @meow(if: Boolean!) on FIELD | QUERY');

        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage('Directive "meow" already exists in the schema. It cannot be redefined.');
        $this->extendSchema($extendedSchema, $sdl);
    }

    // does not allow replacing an existing type

    public function testDoesNotAllowReplacingAnExistingType()
    {
        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage(
            'Field "Bar.foo" already exists in the schema. It cannot also be ' .
            'defined in this type extension.'
        );
        $this->extendTestSchema(dedent('
        extend type Bar {
          foo: Foo
        }
        '));
    }

    // does not allow referencing an unknown type

    public function testDoesNotAllowReferencingAnUnknownType()
    {
        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage(
            'Unknown type: "Quix". Ensure that this type exists either in the ' .
            'original schema, or is added in a type definition.'
        );
        $this->extendTestSchema(dedent('
        extend type Bar {
          quix: Quix
        }
        '));
    }

    // does not allow extending an unknown type

    public function testDoesNotAllowExtendingAnUnknownType()
    {
        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage(
            'Cannot extend type "UnknownType" because it does not exist in the existing schema.'
        );
        $this->extendTestSchema(dedent('
        extend type UnknownType {
          baz: String
        }
        '));
    }

    // does not allow extending an unknown interface type

    public function testDoesNotAllowExtendingAnUnknownInterfaceType()
    {
        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage(
            'Cannot extend type "UnknownInterfaceType" because it does not exist in the existing schema.'
        );
        $this->extendTestSchema(dedent('
        extend interface UnknownInterfaceType {
          baz: String
        }
        '));
    }

    // Skip: maintains configuration of the original schema object

    // Skip: adds to the configuration of the original schema object

    // does not allow extending a non-object type

    // not an object

    public function testDoesNotAllowExtendingANonObjectType()
    {
        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage('Cannot extend non-object type "SomeInterface".');
        $this->extendTestSchema(dedent('
        extend type SomeInterface {
          baz: String
        }
        '));
    }

    // not an interface

    public function testDoesNotAllowExtendingANonObjectInterfaceType()
    {
        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage('Cannot extend non-interface type "Foo".');
        $this->extendTestSchema(dedent('
        extend interface Foo {
          baz: String
        }
        '));
    }

    // not a scalar

    public function testDoesNotAllowExtendingANonObjectScalarType()
    {
        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage('Cannot extend non-object type "String".');
        $this->extendTestSchema(dedent('
        extend type String {
          baz: String
        }
        '));
    }

    // can add additional root operation types

    // does not automatically include common root type names

    public function testDoesNotAutomaticallyIncludeCommonRootTypeNames()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
        type Mutation {
          doSomething: String
        }
        ');

        $schema = $this->extendSchema($this->testSchema, $node);

        $this->assertNull($schema->getMutationType());
    }

    // does not allow new schema within an extension

    public function testDoesNotAllowNewSchemaWithinAnExtension()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
        schema {
          mutation: Mutation
        }
        
        type Mutation {
          doSomething: String
        }
        ');

        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage('Cannot define a new schema within a schema extension.');
        $this->extendSchema($this->testSchema, $node);
    }

    // adds new root types via schema extension

    public function testAddsNewRootTypesViaSchemaExtension()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
        extend schema {
          mutation: Mutation
        }
        
        type Mutation {
          doSomething: String
        }
        ');

        $schema = $this->extendSchema($this->testSchema, $node);

        $this->assertEquals('Mutation', $schema->getMutationType()->getName());
    }

    // adds multiple new root types via schema extension

    public function testAddsMultipleNewRootTypesViaSchemaExtension()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
        extend schema {
          mutation: Mutation
          subscription: Subscription
        }
        
        type Mutation {
          doSomething: String
        }
        
        type Subscription {
          hearSomething: String
        }
        ');

        $schema = $this->extendSchema($this->testSchema, $node);

        $this->assertEquals('Mutation', $schema->getMutationType()->getName());
        $this->assertEquals('Subscription', $schema->getSubscriptionType()->getName());
    }

    // applies multiple schema extensions

    public function testAppliesMultipleSchemaExtension()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
        extend schema {
          mutation: Mutation
        }
        
        extend schema {
          subscription: Subscription
        }
        
        type Mutation {
          doSomething: String
        }
        
        type Subscription {
          hearSomething: String
        }
        ');

        $schema = $this->extendSchema($this->testSchema, $node);

        $this->assertEquals('Mutation', $schema->getMutationType()->getName());
        $this->assertEquals('Subscription', $schema->getSubscriptionType()->getName());
    }

    // TODO: schema extension AST are available from schema object

    // does not allow redefining an existing root type

    public function testDoesNotAllowRedefiningAnExistingRootType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
        extend schema {
          query: SomeType
        }
        
        type SomeType {
          seeSomething: String
        }
        ');

        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage('Must provide only one query type in schema.');
        $this->extendSchema($this->testSchema, $node);
    }

    // does not allow defining a root operation type twice

    public function testDoesNotAllowDefiningARootTypeTwice()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
        extend schema {
          mutation: Mutation
        }
        
        extend schema {
          mutation: Mutation
        }
        
        type Mutation {
          doSomething: String
        }
        ');

        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage('Must provide only one mutation type in schema.');
        $this->extendSchema($this->testSchema, $node);
    }
    
    // does not allow defining a root operation type with different types

    public function testDoesNotAllowDefiningARootTypeWithDifferentTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $node = parse('
        extend schema {
          mutation: Mutation
        }
        
        extend schema {
          mutation: SomethingElse
        }
        
        type Mutation {
          doSomething: String
        }
        
        type SomethingElse {
          doSomethingElse: String
        }
        ');

        $this->expectException(SchemaExtensionException::class);
        $this->expectExceptionMessage('Must provide only one mutation type in schema.');
        $this->extendSchema($this->testSchema, $node);
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
