<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\TypeNameEnum;
use Digia\GraphQL\Type\Definition\UnionType;
use function Digia\GraphQL\Type\booleanType;
use function Digia\GraphQL\Type\intType;
use function Digia\GraphQL\Type\isOutputType;
use function Digia\GraphQL\Type\newEnumType;
use function Digia\GraphQL\Type\newInputObjectType;
use function Digia\GraphQL\Type\newInterfaceType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newScalarType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\newUnionType;
use function Digia\GraphQL\Type\stringType;

class DefinitionTest extends TestCase
{
    /**
     * @var ObjectType
     */
    protected $blogImage;

    /**
     * @var ObjectType
     */
    protected $blogAuthor;

    /**
     * @var ObjectType
     */
    protected $blogArticle;

    /**
     * @var ObjectType
     */
    protected $blogQuery;

    /**
     * @var ObjectType
     */
    protected $blogMutation;

    /**
     * @var ObjectType
     */
    protected $blogSubscription;

    /**
     * @var ObjectType
     */
    protected $objectType;

    /**
     * @var InterfaceType
     */
    protected $interfaceType;

    /**
     * @var UnionType
     */
    protected $unionType;

    /**
     * @var EnumType
     */
    protected $enumType;

    /**
     * @var InputObjectType
     */
    protected $inputObjectType;

    /**
     * @var ScalarType
     */
    protected $scalarType;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->blogImage = newObjectType([
            'name'   => 'Image',
            'fields' => [
                'url'    => ['type' => stringType()],
                'width'  => ['type' => intType()],
                'height' => ['type' => intType()],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->blogAuthor = newObjectType([
            'name'   => 'Author',
            'fields' => function () {
                return [
                    'id'            => ['type' => stringType()],
                    'name'          => ['type' => stringType()],
                    'pic'           => [
                        'type' => $this->blogImage,
                        'args' => [
                            'width'  => ['type' => intType()],
                            'height' => ['type' => intType()],
                        ],
                    ],
                    'recentArticle' => ['type' => $this->blogArticle],
                ];
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->blogArticle = newObjectType([
            'name'   => 'Article',
            'fields' => [
                'id'          => ['type' => stringType()],
                'isPublished' => ['type' => booleanType()],
                'author'      => ['type' => $this->blogAuthor],
                'title'       => ['type' => stringType()],
                'body'        => ['type' => stringType()],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->blogQuery = newObjectType([
            'name'   => 'Query',
            'fields' => [
                'article' => [
                    'args' => ['id' => ['type' => stringType()]],
                    'type' => $this->blogArticle,
                ],
                'feed'    => [
                    'type' => newList($this->blogArticle),
                ],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->blogMutation = newObjectType([
            'name'   => 'Mutation',
            'fields' => [
                'writeArticle' => [
                    'type' => $this->blogArticle,
                ],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->blogSubscription = newObjectType([
            'name'   => 'Subscription',
            'fields' => [
                'articleSubscribe' => [
                    'args' => ['id' => ['type' => stringType()]],
                    'type' => $this->blogArticle,
                ],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->objectType = newObjectType(['name' => 'Object']);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->interfaceType = newInterfaceType(['name' => 'Interface']);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->unionType = newUnionType(['name' => 'Union', 'types' => [$this->objectType]]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->enumType = newEnumType(['name' => 'Enum', 'values' => ['foo' => []]]);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->inputObjectType = newInputObjectType(['name' => 'InputObject']);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->scalarType = newScalarType([
            'name'         => 'Scalar',
            'serialize'    => function () {
            },
            'parseValue'   => function () {
            },
            'parseLiteral' => function () {
            },
        ]);
    }

    /**
     * @param TypeInterface $type
     * @return Schema
     * @throws InvariantException
     */
    protected function schemaWithField(TypeInterface $type): Schema
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'field' => ['type' => $type],
                ],
            ]),
            'types' => [$type],
        ]);
    }

    /**
     * @param mixed $resolveValue
     * @return Schema
     * @throws InvariantException
     */
    protected function schemaWithObjectWithFieldResolver($resolveValue): Schema
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $badResolverType = newObjectType([
            'name'   => 'BadResolver',
            'fields' => [
                'badField' => [
                    'type'    => stringType(),
                    'resolve' => $resolveValue
                ],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        return newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'f' => ['type' => $badResolverType],
                ],
            ])
        ]);
    }

    // Type System: Example

    // defines a query only schema

    public function testQueryOnlySchema()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => $this->blogQuery,
        ]);

        $this->assertEquals($this->blogQuery, $schema->getQueryType());

        /** @noinspection PhpUnhandledExceptionInspection */
        $articleField = $this->blogQuery->getFields()['article'];

        $this->assertEquals($this->blogArticle, $articleField->getType());
        $this->assertEquals($this->blogArticle->getName(), $articleField->getType()->getName());
        $this->assertSame('article', $articleField->getName());

        /** @var ObjectType $articleFieldType */
        $articleFieldType = $articleField->getType();

        /** @noinspection PhpUnhandledExceptionInspection */
        $titleField = $articleFieldType->getFields()['title'];
        $this->assertSame('title', $titleField->getName());
        $this->assertSame(stringType(), $titleField->getType());
        $this->assertSame('String', $titleField->getType()->getName());

        /** @noinspection PhpUnhandledExceptionInspection */
        $authorField = $articleFieldType->getFields()['author'];

        /** @var ObjectType $authorFieldType */
        $authorFieldType = $authorField->getType();

        /** @noinspection PhpUnhandledExceptionInspection */
        $recentArticleField = $authorFieldType->getFields()['recentArticle'];
        $this->assertEquals($this->blogArticle, !$recentArticleField ?: $recentArticleField->getType());

        /** @noinspection PhpUnhandledExceptionInspection */
        $feedField = $this->blogQuery->getFields()['feed'];

        /** @var ListType $feedFieldType */
        $feedFieldType = $feedField->getType();
        $this->assertEquals($this->blogArticle, $feedFieldType->getOfType());

        $this->assertSame('feed', $feedField->getName());
    }

    // defines a mutation schema

    public function testMutationSchema()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'mutation' => $this->blogMutation,
        ]);

        $this->assertEquals($this->blogMutation, $schema->getMutationType());

        /** @noinspection PhpUnhandledExceptionInspection */
        $writeArticleField = $this->blogMutation->getFields()['writeArticle'];

        $this->assertEquals($this->blogArticle, $writeArticleField->getType());
        $this->assertSame('Article', $writeArticleField->getType()->getName());
        $this->assertSame('writeArticle', $writeArticleField->getName());
    }

    // defines a subscription schema

    public function testSubscriptionSchema()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'subscription' => $this->blogSubscription,
        ]);

        $this->assertEquals($this->blogSubscription, $schema->getSubscriptionType());

        /** @noinspection PhpUnhandledExceptionInspection */
        $articleSubscribeField = $this->blogSubscription->getFields()['articleSubscribe'];

        $this->assertEquals($this->blogArticle, $articleSubscribeField->getType());
        $this->assertSame('Article', $articleSubscribeField->getType()->getName());
        $this->assertSame('articleSubscribe', $articleSubscribeField->getName());
    }

    // defines an enum type with deprecated value

    public function testEnumTypeWithDeprecatedValue()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $enumWithDeprecatedValue = newEnumType([
            'name'   => 'EnumWithDeprecatedValue',
            'values' => ['foo' => ['deprecationReason' => 'Just because']],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $enumValue = $enumWithDeprecatedValue->getValues()[0];

        $this->assertSame('foo', $enumValue->getName());
        $this->assertTrue($enumValue->isDeprecated());
        $this->assertSame('Just because', $enumValue->getDeprecationReason());
        $this->assertSame('foo', $enumValue->getValue());
    }

    // defines an enum type with a value of `null`

    public function testEnumTypeWithAValueOfNull()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $enumTypeWithNullValue = newEnumType([
            'name'   => 'EnumWithNullValue',
            'values' => ['NULL' => ['value' => null]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $enumValue = $enumTypeWithNullValue->getValues()[0];
        $this->assertSame('NULL', $enumValue->getName());
        $this->assertNull($enumValue->getDescription());
        $this->assertSame(false, $enumValue->isDeprecated());
        $this->assertNull($enumValue->getValue());
        $this->assertNull($enumValue->getAstNode());
    }

    // defines an object type with deprecated field

    public function testObjectWithDeprecatedField()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $typeWithDeprecatedField = newObjectType([
            'name'   => 'foo',
            'fields' => [
                'bar' => [
                    'type'              => stringType(),
                    'deprecationReason' => 'A terrible reason',
                ],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $field = $typeWithDeprecatedField->getFields()['bar'];

        $this->assertSame(stringType(), $field->getType());
        $this->assertSame('A terrible reason', $field->getDeprecationReason());
        $this->assertTrue($field->isDeprecated());
        $this->assertSame('bar', $field->getName());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEmpty($field->getArguments());
    }

    // includes nested input objects in the map

    public function testIncludesNestedInputObjectsInSchemaTypeMap()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $nestedInputObject = newInputObjectType([
            'name'   => 'NestedInputObject',
            'fields' => ['value' => ['type' => stringType()]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $someInputObject = newInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => ['nested' => ['type' => $nestedInputObject]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $someMutation = newObjectType([
            'name'   => 'SomeMutation',
            'fields' => [
                'mutateSomething' => [
                    'type' => $this->blogArticle,
                    'args' => ['input' => ['type' => $someInputObject]],
                ],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $someSubscription = newObjectType([
            'name'   => 'SomeSubscription',
            'fields' => [
                'subscribeToSomething' => [
                    'type' => $this->blogArticle,
                    'args' => ['input' => ['type' => $someInputObject]],
                ],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query'        => $this->blogQuery,
            'mutation'     => $someMutation,
            'subscription' => $someSubscription,
        ]);

        $this->assertEquals($nestedInputObject, $schema->getTypeMap()[$nestedInputObject->getName()]);
    }

    // includes interface possible types in the type map

    public function testIncludesInterfacePossibleTypesInSchemaTypeMap()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $someInterface = newInterfaceType([
            'name'   => 'SomeInterface',
            'fields' => [
                'f' => ['type' => intType()],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $someSubtype = newObjectType([
            'name'       => 'SomeSubtype',
            'fields'     => [
                'f' => ['type' => intType()],
            ],
            'interfaces' => [$someInterface],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'iface' => ['type' => $someInterface],
                ],
            ]),
            'types' => [$someSubtype],
        ]);

        $this->assertEquals($someSubtype, $schema->getTypeMap()[$someSubtype->getName()]);
    }

    // includes interfaces' thunk subtypes in the type map

    public function testIncludesInterfacesThunkSubtypesInTheSchemaTypeMap()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $someInterface = newInterfaceType([
            'name'   => 'SomeInterface',
            'fields' => ['f' => ['type' => intType()]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $someSubtype = newObjectType([
            'name'       => 'SomeSubtype',
            'fields'     => ['f' => ['type' => intType()]],
            'interfaces' => function () use ($someInterface) {
                return [$someInterface];
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => ['iface' => ['type' => $someInterface]],
            ]),
            'types' => [$someSubtype],
        ]);

        $this->assertEquals($someSubtype, $schema->getTypeMap()[$someSubtype->getName()]);
    }

    // stringifies simple types

    public function testStringifySimpleTypes()
    {
        $this->assertEquals(TypeNameEnum::INT, (string)intType());
        $this->assertEquals('Article', (string)$this->blogArticle);
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('Interface', (string)newInterfaceType(['name' => 'Interface']));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('Union', (string)newUnionType(['name' => 'Union']));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('Enum', (string)newEnumType(['name' => 'Enum']));
        $this->assertEquals(TypeNameEnum::INT, (string)intType());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('Int!', (string)newNonNull(intType()));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('[Int]!', (string)newNonNull(newList(intType())));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('[Int!]', (string)newList(newNonNull(intType())));
        $this->assertEquals('[[Int]]', (string)newList(newList(intType())));
    }

    // identifies input types

    /**
     * @param TypeInterface|null $type
     * @param mixed              $answer
     * @dataProvider identifiesInputTypesDataProvider
     * @throws \Digia\GraphQL\Error\InvalidTypeException
     */
    public function testIdentifiesInputTypes(?TypeInterface $type, $answer)
    {
        $this->assertEquals($answer, isOutputType($type));
        $this->assertEquals($answer, isOutputType(newList($type)));
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($answer, isOutputType(newNonNull($type)));
    }

    public function identifiesInputTypesDataProvider(): array
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        // We cannot use the class fields here because they do not get instantiated for data providers.
        return [
            [intType(), true],
            [newObjectType(['name' => 'Object']), true],
            [newInterfaceType(['name' => 'Interface']), true],
            [newUnionType(['name' => 'Union']), true],
            [newEnumType(['name' => 'Enum']), true],
            [newInputObjectType(['name' => 'InputObjectType']), false],
        ];
    }

    // prohibits nesting NonNull inside NonNull

    public function testProhibitsNestingNonNullInsideNonNull()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('Expected Int! to be a GraphQL nullable type.');

        /** @noinspection PhpUnhandledExceptionInspection */
        newNonNull(newNonNull(intType()));

        $this->addToAssertionCount(1);
    }

    // allows a thunk for Union member types

    public function testAllowsAThunkForUnionMemberTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $union = newUnionType([
            'name'  => 'ThunkUnion',
            'types' => function () {
                return [$this->objectType];
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $types = $union->getTypes();

        $this->assertCount(1, $types);
        $this->assertEquals($this->objectType, $types[0]);
    }

    // does not mutate passed field definitions

    public function testDoesNotMutatePassedFieldDefinitions()
    {
        $fields = [
            'field1' => ['type' => stringType()],
            'field2' => [
                'type' => stringType(),
                'args' => [
                    'id' => ['type' => stringType()],
                ],
            ],
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $testObject1 = newObjectType([
            'name'   => 'Test1',
            'fields' => $fields,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $testObject2 = newObjectType([
            'name'   => 'Test2',
            'fields' => $fields,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($testObject2->getFields(), $testObject1->getFields());

        /** @noinspection PhpUnhandledExceptionInspection */
        $testInputObject1 = newInputObjectType([
            'name'   => 'Test1',
            'fields' => $fields,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $testInputObject2 = newInputObjectType([
            'name'   => 'Test2',
            'fields' => $fields,
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($testInputObject2->getFields(), $testInputObject1->getFields());

        $this->assertEquals(stringType(), $fields['field1']['type']);
        $this->assertEquals(stringType(), $fields['field2']['type']);
        $this->assertEquals(stringType(), $fields['field2']['args']['id']['type']);
    }

    // Field config must be object

    // accepts an Object type with a field function

    public function testAcceptsAnObjectTypeWithAFieldFunction()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objectType = newObjectType([
            'name'   => 'SomeObject',
            'fields' => function () {
                return ['f' => ['type' => stringType()]];
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(stringType(), $objectType->getField('f')->getType());
    }

    // rejects an Object type field with undefined config

    public function testRejectsAnObjectTypeFieldWithNullConfig()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objType = newObjectType([
            'name'   => 'SomeObject',
            'fields' => ['f' => null],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('SomeObject.f field config must be an associative array.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    // rejects an Object type with incorrectly typed fields

    function testRejectsAnObjectTypeWithIncorrectlyTypedFields()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objType = newObjectType([
            'name'   => 'SomeObject',
            'fields' => [['f' => null]],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeObject fields must be an associative array with field names as keys or a ' .
            'callable which returns such an array.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    // rejects an Object type with a field function that returns incorrect type

    public function testRejectsAnObjectTypeWithAFieldFunctionThatReturnsIncorrectType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objType = newObjectType([
            'name'   => 'SomeObject',
            'fields' => function () {
                return [['f' => null]];
            },
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeObject fields must be an associative array with field names as keys or a ' .
            'callable which returns such an array.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    // Field arg config must be object

    // accepts an Object type with field args

    public function testAcceptsAnObjectTypeWithFieldArgs()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objType = newObjectType([
            'name'   => 'SomeObject',
            'fields' => [
                'goodField' => [
                    'type' => stringType(),
                    'args' => [
                        'goodArg' => ['type' => stringType()],
                    ],
                ],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    // rejects an Object type with incorrectly typed field args

    public function testRejectsAnObjectTypeWithIncorrectlyTypedFieldArgs()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objType = newObjectType([
            'name'   => 'SomeObject',
            'fields' => [
                'badField' => [
                    'type' => stringType(),
                    'args' => [['badArg' => stringType()]],
                ],
            ],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('SomeObject.badField args must be an object with argument names as keys.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    // does not allow isDeprecated without deprecationReason on field

    public function testDoesNotAllowIsDeprecatedWithoutDeprecationReasonOnField()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $oldObject = newObjectType([
            'name'   => 'OldObject',
            'fields' => [
                'field' => [
                    'type'         => stringType(),
                    'isDeprecated' => true,
                ],
            ],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('OldObject.field should provide "deprecationReason" instead of "isDeprecated".');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField($oldObject);

        $this->addToAssertionCount(1);
    }

    // Object interfaces must be array

    // accepts an Object type with array interfaces

    public function testAcceptsAnObjectTypeWithArrayInterfaces()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objType = newObjectType([
            'name'       => 'SomeObject',
            'interfaces' => [$this->interfaceType],
            'fields'     => ['f' => ['type' => stringType()]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($this->interfaceType, $objType->getInterfaces()[0]);
    }

    // accepts an Object type with interfaces as a function returning an array

    public function testAcceptsAnObjectTypeWithInterfacesAsAFunctionReturningAnArray()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objType = newObjectType([
            'name'       => 'SomeObject',
            'interfaces' => [$this->interfaceType],
            'fields'     => [
                'f' => function () {
                    return ['type' => stringType()];
                },
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals($this->interfaceType, $objType->getInterfaces()[0]);
    }

    // rejects an Object type with incorrectly typed interfaces

    public function testRejectsAnObjectTypeWithIncorrectlyTypedInterfaces()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objType = newObjectType([
            'name'       => 'SomeObject',
            'interfaces' => '',
            'fields'     => ['f' => ['type' => stringType()]],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('SomeObject interfaces must be an array or a function which returns an array.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $objType->getInterfaces();
    }

    // rejects an Object type with interfaces as a function returning an incorrect type

    public function testRejectsAnObjectTypeWithInterfacesAsAFunctionReturningAnIncorrectType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objType = newObjectType([
            'name'       => 'SomeObject',
            'interfaces' => function () {
                return '';
            },
            'fields'     => ['f' => ['type' => stringType()]],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('SomeObject interfaces must be an array or a function which returns an array.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $objType->getInterfaces();
    }

    // Type System: Object fields must have valid resolve values

    // accepts a lambda as an Object field resolver

    public function testAcceptsALambdaAsAnObjectFieldResolver()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithObjectWithFieldResolver(function () {
            return [];
        });

        $this->addToAssertionCount(1);
    }

    // rejects an empty Object field resolver

    public function testRejectsAnEmptyArrayFieldResolver()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'BadResolver.badField field resolver must be a function if provided, but got: Array.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithObjectWithFieldResolver([]);

        $this->addToAssertionCount(1);
    }

    // ejects a constant scalar value resolver

    public function testRejectsAnScalarFieldResolver()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'BadResolver.badField field resolver must be a function if provided, but got: 0.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithObjectWithFieldResolver(0);

        $this->addToAssertionCount(1);
    }

    // Type System: Interface types must be resolvable

    // accepts an Interface type defining resolveType

    public function testAcceptsAnInterfaceTypeDefiningResolveType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $anotherInterfaceType = newInterfaceType([
            'name'        => 'AnotherInterface',
            'fields'      => ['f' => ['type' => stringType()]],
            'resolveType' => function () {
                return '';
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newObjectType([
                'name'       => 'SomeObject',
                'interfaces' => [$anotherInterfaceType],
                'fields'     => ['f' => ['type' => stringType()]],
            ])
        );

        $this->addToAssertionCount(1);
    }

    // accepts an Interface with implementing type defining isTypeOf

    public function testAcceptsAnInterfaceTypeWithImplementingTypeDefiningIsTypeOf()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $anotherInterfaceType = newInterfaceType([
            'name'   => 'AnotherInterface',
            'fields' => ['f' => ['type' => stringType()]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newObjectType([
                'name'       => 'SomeObject',
                'interfaces' => [$anotherInterfaceType],
                'fields'     => ['f' => ['type' => stringType()]],
                'isTypeOf'   => function () {
                    return true;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    // accepts an Interface type defining resolveType with implementing type defining isTypeOf

    public function testAcceptsAnInterfaceTypeDefiningResolveTypeWithImplementingTypeDefiningIsTypeOf()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $anotherInterfaceType = newInterfaceType([
            'name'        => 'AnotherInterface',
            'fields'      => ['f' => ['type' => stringType()]],
            'resolveType' => function () {
                return '';
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newObjectType([
                'name'       => 'SomeObject',
                'interfaces' => [$anotherInterfaceType],
                'fields'     => ['f' => ['type' => stringType()]],
                'isTypeOf'   => function () {
                    return true;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    // rejects an Interface type with an incorrect type for resolveType

    public function testRejectsAnInterfaceTypeWithAnIncorrectTypeForResolveType()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('AnotherInterface must provide "resolveType" as a function.');
        /** @noinspection PhpUnhandledExceptionInspection */
        newInterfaceType([
            'name'        => 'AnotherInterface',
            'resolveType' => '',
            'fields'      => ['f' => ['type' => stringType()]],
        ]);
    }

    // Type System: Union types must be resolvable

    // accepts a Union type defining resolveType

    public function testAcceptsUnionTypeDefiningResolveType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newUnionType([
                'name'  => 'SomeUnion',
                'types' => [$this->objectType],
            ])
        );

        $this->addToAssertionCount(1);
    }

    // accepts a Union of Object types defining isTypeOf

    public function testAcceptsAUnionOfObjectTypesDefiningIsTypeOf()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objectWithIsTypeOf = newObjectType([
            'name'     => 'ObjectWithIsTypeOf',
            'fields'   => ['f' => ['type' => stringType()]],
            'isTypeOf' => function () {
                return true;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newUnionType([
                'name'  => 'SomeUnion',
                'types' => [$objectWithIsTypeOf],
            ])
        );

        $this->addToAssertionCount(1);
    }

    // accepts a Union type defining resolveType of Object types defining isTypeOf

    public function testAcceptsAUnionTypeDefiningResolveTypeOfObjectTypesDefiningIsTypeOf()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objectWithIsTypeOf = newObjectType([
            'name'     => 'ObjectWithIsTypeOf',
            'fields'   => ['f' => ['type' => stringType()]],
            'isTypeOf' => function () {
                return true;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newUnionType([
                'name'        => 'SomeUnion',
                'types'       => [$objectWithIsTypeOf],
                'resolveType' => function () {
                    return 'ObjectWithIsTypeOf';
                }
            ])
        );

        $this->addToAssertionCount(1);
    }

    // rejects an Interface type with an incorrect type for resolveType

    public function testRejectsAnInterfaceWithAnIncorrectTypeForResolveType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $objectWithIsTypeOf = newObjectType([
            'name'     => 'ObjectWithIsTypeOf',
            'fields'   => ['f' => ['type' => stringType()]],
            'isTypeOf' => function () {
                return true;
            }
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('SomeUnion must provide "resolveType" as a function.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newUnionType([
                'name'        => 'SomeUnion',
                'types'       => [$objectWithIsTypeOf],
                'resolveType' => ''
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Scalar types must be serializable

    // accepts a Scalar type defining serialize

    public function testAcceptsAScalarTypeDefiningSerialize()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newScalarType([
                'name'      => 'SomeScalar',
                'serialize' => function () {
                    return null;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    // rejects a Scalar type not defining serialize

    public function testRejectsAScalarTypeNotDefiningSerialize()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeScalar must provide "serialize" function. If this custom Scalar ' .
            'is also used as an input type, ensure "parseValue" and "parseLiteral" ' .
            'functions are also provided.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newScalarType([
                'name' => 'SomeScalar',
            ])
        );

        $this->addToAssertionCount(1);
    }

    // rejects a Scalar type defining serialize with an incorrect type

    public function testRejectsAScalarTypeDefiningSerializeWithAnIncorrectType()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeScalar must provide "serialize" function. If this custom Scalar ' .
            'is also used as an input type, ensure "parseValue" and "parseLiteral" ' .
            'functions are also provided.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newScalarType([
                'name'      => 'SomeScalar',
                'serialize' => '',
            ])
        );

        $this->addToAssertionCount(1);
    }

    // accepts a Scalar type defining parseValue and parseLiteral

    public function testAcceptsAScalarTypeDefiningParseValueAndParseLiteral()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newScalarType([
                'name'         => 'SomeScalar',
                'serialize'    => function () {
                    return null;
                },
                'parseValue'   => function () {
                    return null;
                },
                'parseLiteral' => function () {
                    return null;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    // rejects a Scalar type defining parseValue but not parseLiteral

    public function testRejectsAScalarTypeDefiningParseValueButNotParseLiteral()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('SomeScalar must provide both "parseValue" and "parseLiteral" functions.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newScalarType([
                'name'       => 'SomeScalar',
                'serialize'  => function () {
                    return null;
                },
                'parseValue' => function () {
                    return null;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    // rejects a Scalar type defining parseLiteral but not parseValue

    public function testRejectsAScalarTypeDefiningParseLiteralButNotParseValue()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('SomeScalar must provide both "parseValue" and "parseLiteral" functions.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newScalarType([
                'name'         => 'SomeScalar',
                'serialize'    => function () {
                    return null;
                },
                'parseLiteral' => function () {
                    return null;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    // rejects a Scalar type defining parseValue and parseLiteral with an incorrect type

    public function testRejectsAScalarTypeDefiningParseValueAndParseLiteralWithAnIncorrectType()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('SomeScalar must provide both "parseValue" and "parseLiteral" functions.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newScalarType([
                'name'         => 'SomeScalar',
                'serialize'    => function () {
                    return null;
                },
                'parseValue'   => '',
                'parseLiteral' => '',
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Object types must be assertable

    // accepts an Object type with an isTypeOf function

    public function testAcceptsAnObjectTypeWithAnIsTypeOfFunction()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newObjectType([
                'name'     => 'AnotherObject',
                'fields'   => ['f' => ['type' => stringType()]],
                'isTypeOf' => function () {
                    return true;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    // rejects an Object type with an incorrect type for isTypeOf

    public function testRejectsAnObjectTypeWithAnIncorrectTypeForIsTypeOf()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('AnotherObject must provide "isTypeOf" as a function.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newObjectType([
                'name'     => 'AnotherObject',
                'fields'   => ['f' => ['type' => stringType()]],
                'isTypeOf' => '',
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Union types must be array

    // accepts a Union type with array types

    public function testAcceptsAnUnionTypeWithArrayTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newUnionType([
                'name'  => 'SomeUnion',
                'types' => [$this->objectType],
            ])
        );

        $this->addToAssertionCount(1);
    }

    // accepts a Union type with function returning an array of types

    public function testAcceptsAnUnionTypeWithFunctionReturningAnArrayOfTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newUnionType([
                'name'  => 'SomeUnion',
                'types' => function () {
                    return [$this->objectType];
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    // rejects a Union type without types

    public function testRejectsAnUnionWithoutTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newUnionType([
                'name' => 'SomeUnion',
            ])
        );

        $this->addToAssertionCount(1);
    }

    // rejects a Union type with incorrectly typed types

    public function testRejectsAUnionTypeWithIncorrectlyTypedTypes()
    {
        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'Must provide array of types or a function which returns such an array for Union SomeUnion.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schemaWithField(
            newUnionType([
                'name'  => 'SomeUnion',
                'types' => ''
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Input Objects must have fields

    // accepts an Input Object type with fields

    public function testAcceptsAnInputObjectTypeWithFields()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $inputObjectType = newInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => ['f' => ['type' => stringType()]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $field = $inputObjectType->getField('f');
        $this->assertEquals(stringType(), $field->getType());
    }

    // accepts an Input Object type with a field function

    public function testAcceptsAnInputObjectTypeWithAFieldFunction()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $inputObjectType = newInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => function () {
                return ['f' => ['type' => stringType()]];
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $field = $inputObjectType->getField('f');
        $this->assertEquals(stringType(), $field->getType());
    }

    // rejects an Input Object type with incorrect fields

    public function testRejectsAnInputObjectTypeWithIncorrectFields()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $inputObjectType = newInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => '',
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeInputObject fields must be an associative array with field names as keys or a ' .
            'function which returns such an array.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $inputObjectType->getField('f');
    }

    // rejects an Input Object type with fields function that returns incorrect type

    public function testRejectsAnInputObjectTypeWithFieldsFunctionThatReturnsIncorrectType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $inputObjectType = newInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => function () {
                return '';
            },
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeInputObject fields must be an associative array with field names as keys or a ' .
            'function which returns such an array.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $inputObjectType->getField('f');
    }

    // Type System: Input Object fields must not have resolvers

    // rejects an Input Object type with resolvers

    public function testRejectsAnInputObjectTypeWithResolvers()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $inputObjectType = newInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => [
                'f' => [
                    'type'    => stringType(),
                    'resolve' => function () {
                        return 0;
                    }
                ],
            ],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeInputObject.f field type has a resolve property, ' .
            'but Input Types cannot define resolvers.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $inputObjectType->getFields();

        $this->addToAssertionCount(1);
    }

    // rejects an Input Object type with resolver constant

    public function testRejectsAnInputObjectTypeWithIncorrectlyTypedResolver()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $inputObjectType = newInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => [
                'f' => [
                    'type'    => stringType(),
                    'resolve' => ''
                ],
            ],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeInputObject.f field type has a resolve property, ' .
            'but Input Types cannot define resolvers.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $inputObjectType->getFields();

        $this->addToAssertionCount(1);
    }

    // Type System: Enum types must be well defined

    // accepts a well defined Enum type with empty value definition

    public function testAcceptsAWellDefinedEnumTypeWithEmptyValueDefinition()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $enumType = newEnumType([
            'name'   => 'SomeEnum',
            'values' => [
                'FOO' => [],
                'BAR' => [],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('FOO', $enumType->getValue('FOO')->getValue());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals('BAR', $enumType->getValue('BAR')->getValue());
    }

    // accepts a well defined Enum type with internal value definition

    public function testAcceptsAWellDefinedEnumTypeWithInternalValueDefinition()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $enumType = newEnumType([
            'name'   => 'SomeEnum',
            'values' => [
                'FOO' => ['value' => 10],
                'BAR' => ['value' => 20],
            ],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(10, $enumType->getValue('FOO')->getValue());
        /** @noinspection PhpUnhandledExceptionInspection */
        $this->assertEquals(20, $enumType->getValue('BAR')->getValue());
    }

    // rejects an Enum type with incorrectly typed values

    public function testRejectsAnEnumWithIncorrectlyTypedValues()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $enumType = newEnumType([
            'name'   => 'SomeEnum',
            'values' => [['FOO' => 10]],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage('SomeEnum values must be an associative array with value names as keys.');

        /** @noinspection PhpUnhandledExceptionInspection */
        $enumType->getValues();

        $this->addToAssertionCount(1);
    }

    // rejects an Enum type with missing value definition

    public function testRejectsAnEnumTypeWithMissingValueDefinition()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $enumType = newEnumType([
            'name'   => 'SomeEnum',
            'values' => ['FOO' => null],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeEnum.FOO must refer to an object with a "value" key representing ' .
            'an internal value but got: null.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $enumType->getValues();

        $this->addToAssertionCount(1);
    }

    // rejects an Enum type with incorrectly typed value definition

    public function testRejectsAnEnumTypeWithIncorrectlyTypedValueDefinition()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $enumType = newEnumType([
            'name'   => 'SomeEnum',
            'values' => ['FOO' => 10],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeEnum.FOO must refer to an object with a "value" key representing ' .
            'an internal value but got: 10.'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $enumType->getValues();

        $this->addToAssertionCount(1);
    }

    // does not allow isDeprecated without deprecationReason on enum

    public function testDoesNotAllowIsDeprecatedWithoutDeprecationReasonOnEnum()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $enumType = newEnumType([
            'name'   => 'SomeEnum',
            'values' => ['FOO' => ['isDeprecated' => true]],
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'SomeEnum.FOO should provide "deprecationReason" instead of "isDeprecated".'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        $enumType->getValues();

        $this->addToAssertionCount(1);
    }

    // Type System: List must accept only types

    // accepts an type as item type of list

    public function testAcceptsTypesAsItemOfList()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $types = [
            stringType(),
            $this->scalarType,
            $this->objectType,
            $this->unionType,
            $this->interfaceType,
            $this->enumType,
            $this->inputObjectType,
            newList(stringType()),
            newNonNull(stringType()),
        ];

        foreach ($types as $type) {
            /** @noinspection PhpUnhandledExceptionInspection */
            newList($type);
            $this->addToAssertionCount(1);
        }
    }

    // rejects a non-type as item type of list

    public function testRejectsANonTypesAsItemTypeOfList()
    {
        $notTypes = [
            'Object'         => new \stdClass(),
            'Array'          => [],
            'Function'       => function () {
                return null;
            },
            '(empty string)' => '',
            'null'           => null,
            'true'           => true,
            'false'          => false,
            'String'         => 'String',
        ];

        foreach ($notTypes as $string => $notType) {
            $this->expectException(InvariantException::class);
            $this->expectExceptionMessage(\sprintf('Expected %s to be a GraphQL type.', $string));

            /** @noinspection PhpUnhandledExceptionInspection */
            newList($notType);
            $this->addToAssertionCount(1);
        }
    }

    // Type System: NonNull must only accept non-nullable types

    // accepts an type as nullable type of non-null

    public function testAcceptsTypesAsNullableTypeOfNonNull()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $types = [
            stringType(),
            $this->scalarType,
            $this->objectType,
            $this->unionType,
            $this->interfaceType,
            $this->enumType,
            $this->inputObjectType,
            newList(stringType()),
            newList(newNonNull(stringType())),
        ];

        foreach ($types as $type) {
            /** @noinspection PhpUnhandledExceptionInspection */
            newNonNull($type);
            $this->addToAssertionCount(1);
        }
    }

    // rejects a non-type as nullable type of non-null

    public function testRejectsNonTypesAsNullableTypeOfNonNull()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $nonTypes = [
            'String!'        => newNonNull(stringType()),
            'Object'         => new \stdClass(),
            'Array'          => [],
            'Function'       => function () {
                return null;
            },
            '(empty string)' => '',
            'null'           => null,
            'true'           => true,
            'false'          => false,
            'String'         => 'String',
        ];

        foreach ($nonTypes as $string => $nonType) {
            $this->expectException(InvariantException::class);
            $this->expectExceptionMessage(\sprintf('Expected %s to be a GraphQL nullable type.', $string));

            /** @noinspection PhpUnhandledExceptionInspection */
            newNonNull($nonType);
            $this->addToAssertionCount(1);
        }
    }

    // Type System: A Schema must contain uniquely named types

    // rejects a Schema which redefines a built-in type

    public function testRejectsASchemaWhichDefinesABuiltInType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $fakeString = newScalarType([
            'name'      => 'String',
            'serialize' => function () {
                return null;
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $queryType = newObjectType([
            'name'   => 'Query',
            'fields' => [
                'normal' => ['type' => stringType()],
                'fake'   => ['type' => $fakeString],
            ]
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'Schema must contain unique named types but contains multiple types named "String".'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        newSchema(['query' => $queryType]);

        $this->addToAssertionCount(1);
    }

    // rejects a Schema which defines an object type twice

    public function testRejectsASchemaWhichDefinesAnObjectTypeTwice()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $a = newObjectType([
            'name'   => 'SameName',
            'fields' => ['f' => ['type' => stringType()]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $b = newObjectType([
            'name'   => 'SameName',
            'fields' => ['f' => ['type' => stringType()]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $queryType = newObjectType([
            'name'   => 'Query',
            'fields' => [
                'a' => ['type' => $a],
                'b' => ['type' => $b],
            ]
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'Schema must contain unique named types but contains multiple types named "SameName".'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        newSchema(['query' => $queryType]);

        $this->addToAssertionCount(1);
    }

    // rejects a Schema which have same named objects implementing an interface

    public function testRejectsASchemaWhichHaveSameNamedObjectsImplementingAnInterface()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $anotherInterface = newInterfaceType([
            'name'   => 'AnotherInterface',
            'fields' => ['f' => ['type' => stringType()]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $firstBadObject = newObjectType([
            'name'       => 'BadObject',
            'interfaces' => [$anotherInterface],
            'fields'     => ['f' => ['type' => stringType()]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $secondBadObject = newObjectType([
            'name'       => 'BadObject',
            'interfaces' => [$anotherInterface],
            'fields'     => ['f' => ['type' => stringType()]],
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $queryType = newObjectType([
            'name'   => 'Query',
            'fields' => [
                'iface' => ['type' => $anotherInterface],
            ]
        ]);

        $this->expectException(InvariantException::class);
        $this->expectExceptionMessage(
            'Schema must contain unique named types but contains multiple types named "BadObject".'
        );

        /** @noinspection PhpUnhandledExceptionInspection */
        newSchema([
            'query' => $queryType,
            'types' => [$firstBadObject, $secondBadObject],
        ]);

        $this->addToAssertionCount(1);
    }
}
