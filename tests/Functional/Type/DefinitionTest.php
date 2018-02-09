<?php

namespace Digia\GraphQL\Type;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\Scalar\ScalarType;
use Digia\GraphQL\Type\Definition\Scalar\StringType;
use Digia\GraphQL\Type\Definition\TypeEnum;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Type\Schema\Schema;

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
     * @throws \TypeError
     */
    public function setUp()
    {
        $this->blogImage = GraphQLObjectType([
            'name'   => 'Image',
            'fields' => [
                'url'    => ['type' => GraphQLString()],
                'width'  => ['type' => GraphQLInt()],
                'height' => ['type' => GraphQLInt()],
            ],
        ]);

        $this->blogAuthor = GraphQLObjectType([
            'name'   => 'Author',
            'fields' => function () {
                return [
                    'id'            => ['type' => GraphQLString()],
                    'name'          => ['type' => GraphQLString()],
                    'pic'           => [
                        'type' => $this->blogImage,
                        'args' => [
                            'width'  => ['type' => GraphQLInt()],
                            'height' => ['type' => GraphQLInt()],
                        ],
                    ],
                    'recentArticle' => ['type' => $this->blogArticle],
                ];
            },
        ]);

        $this->blogArticle = GraphQLObjectType([
            'name'   => 'Article',
            'fields' => [
                'id'          => ['type' => GraphQLString()],
                'isPublished' => ['type' => GraphQLBoolean()],
                'author'      => ['type' => $this->blogAuthor],
                'title'       => ['type' => GraphQLString()],
                'body'        => ['type' => GraphQLString()],
            ],
        ]);

        $this->blogQuery = GraphQLObjectType([
            'name'   => 'Query',
            'fields' => [
                'article' => [
                    'args' => ['id' => ['type' => GraphQLString()]],
                    'type' => $this->blogArticle,
                ],
                'feed'    => [
                    'type' => GraphQLList($this->blogArticle),
                ],
            ],
        ]);

        $this->blogMutation = GraphQLObjectType([
            'name'   => 'Mutation',
            'fields' => [
                'writeArticle' => [
                    'type' => $this->blogArticle,
                ],
            ],
        ]);

        $this->blogSubscription = GraphQLObjectType([
            'name'   => 'Subscription',
            'fields' => [
                'articleSubscribe' => [
                    'args' => ['id' => ['type' => GraphQLString()]],
                    'type' => $this->blogArticle,
                ],
            ],
        ]);

        $this->objectType      = GraphQLObjectType(['name' => 'Object']);
        $this->interfaceType   = GraphQLInterfaceType(['name' => 'Interface']);
        $this->unionType       = GraphQLUnionType(['name' => 'Union', 'types' => [$this->objectType]]);
        $this->enumType        = GraphQLEnumType(['name' => 'Enum', 'values' => ['foo' => []]]);
        $this->inputObjectType = GraphQLInputObjectType(['name' => 'InputObject']);
        $this->scalarType      = GraphQLScalarType([
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
     * @throws \Exception
     */
    protected function schemaWithField(TypeInterface $type): Schema
    {
        return GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'field' => ['type' => $type],
                ],
            ]),
            'types' => [$type],
        ])->build();
    }

    /**
     * @param $resolveValue
     * @return Schema
     * @throws \Exception
     */
    protected function schemaWithObjectWithFieldResolver($resolveValue): Schema
    {
        $badResolverType = GraphQLObjectType([
            'name'   => 'BadResolver',
            'fields' => [
                'badField' => [
                    'type'    => GraphQLString(),
                    'resolve' => $resolveValue
                ],
            ],
        ]);

        return GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'f' => ['type' => $badResolverType],
                ],
            ])
        ])->build();
    }

    // Type System: Example

    /**
     * @throws \Exception
     */
    public function testQuerySchema()
    {
        $schema = GraphQLSchema([
            'query' => $this->blogQuery,
        ]);

        $this->assertEquals($this->blogQuery, $schema->getQuery());

        $articleField = $this->blogQuery->getField('article');

        $this->assertEquals($this->blogArticle, !$articleField ?: $articleField->getType());
        $this->assertEquals($this->blogArticle->getName(), $articleField->getType()->getName());
        $this->assertEquals('article', $articleField->getName());

        /** @var ObjectType $articleFieldType */
        $articleFieldType = $articleField->getType();

        $titleField = $articleFieldType->getField('title');
        $this->assertEquals('title', !$titleField ?: $titleField->getName());
        $this->assertInstanceOf(StringType::class, $titleField->getType());
        $this->assertEquals('String', $titleField->getType()->getName());

        $authorField = $articleFieldType->getField('author');

        /** @var ObjectType $authorFieldType */
        $authorFieldType = !$authorField ?: $authorField->getType();

        $recentArticleField = $authorFieldType->getField('recentArticle');
        $this->assertEquals($this->blogArticle, !$recentArticleField ?: $recentArticleField->getType());

        $feedField = $this->blogQuery->getField('feed');

        /** @var ListType $feedFieldType */
        $feedFieldType = !$feedField ?: $feedField->getType();
        $this->assertEquals($this->blogArticle, $feedFieldType->getOfType());

        $this->assertEquals('feed', $feedField->getName());
    }

    /**
     * @throws \Exception
     */
    public function testMutationSchema()
    {
        $schema = GraphQLSchema([
            'mutation' => $this->blogMutation,
        ]);

        $this->assertEquals($this->blogMutation, $schema->getMutation());

        $writeArticleField = $this->blogMutation->getField('writeArticle');

        $this->assertEquals($this->blogArticle, !$writeArticleField ?: $writeArticleField->getType());
        $this->assertEquals('Article', $writeArticleField->getType()->getName());
        $this->assertEquals('writeArticle', $writeArticleField->getName());
    }

    /**
     * @throws \Exception
     */
    public function testSubscriptionSchema()
    {
        $schema = GraphQLSchema([
            'subscription' => $this->blogSubscription,
        ]);

        $this->assertEquals($this->blogSubscription, $schema->getSubscription());

        $articleSubscribeField = $this->blogSubscription->getField('articleSubscribe');

        $this->assertEquals($this->blogArticle, !$articleSubscribeField ?: $articleSubscribeField->getType());
        $this->assertEquals('Article', $articleSubscribeField->getType()->getName());
        $this->assertEquals('articleSubscribe', $articleSubscribeField->getName());
    }

    /**
     * @throws \Exception
     */
    public function testEnumTypeWithDeprecatedValue()
    {
        $enumWithDeprecatedValue = GraphQLEnumType([
            'name'   => 'EnumWithDeprecatedValue',
            'values' => ['foo' => ['deprecationReason' => 'Just because']],
        ]);

        /** @var EnumValue $enumValue */
        $enumValue = $enumWithDeprecatedValue->getValues()[0];

        $this->assertEquals('foo', $enumValue->getName());
        $this->assertTrue($enumValue->isDeprecated());
        $this->assertEquals('Just because', $enumValue->getDeprecationReason());
        $this->assertEquals('foo', $enumValue->getValue());
    }

    // TODO: Assess if we want to test "defines an enum type with a value of `null` and `undefined`".

    /**
     * @throws \Exception
     */
    public function testObjectWithDeprecatedField()
    {
        $typeWithDeprecatedField = GraphQLObjectType([
            'name'   => 'foo',
            'fields' => [
                'bar' => [
                    'type'              => GraphQLString(),
                    'deprecationReason' => 'A terrible reason',
                ],
            ],
        ]);

        $field = $typeWithDeprecatedField->getField('bar');

        $this->assertInstanceOf(StringType::class, !$field ?: $field->getType());
        $this->assertEquals('A terrible reason', $field->getDeprecationReason());
        $this->assertTrue($field->isDeprecated());
        $this->assertEquals('bar', $field->getName());
        $this->assertEmpty($field->getArgs());
    }

    /**
     * @throws \Exception
     */
    public function testIncludesNestedInputObjectsInSchemaTypeMap()
    {
        $nestedInputObject = GraphQLInputObjectType([
            'name'   => 'NestedInputObject',
            'fields' => ['value' => ['type' => GraphQLString()]],
        ]);

        $someInputObject = GraphQLInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => ['nested' => ['type' => $nestedInputObject]],
        ]);

        $someMutation = GraphQLObjectType([
            'name'   => 'SomeMutation',
            'fields' => [
                'mutateSomething' => [
                    'type' => $this->blogArticle,
                    'args' => ['input' => ['type' => $someInputObject]],
                ],
            ],
        ]);

        $someSubscription = GraphQLObjectType([
            'name'   => 'SomeSubscription',
            'fields' => [
                'subscribeToSomething' => [
                    'type' => $this->blogArticle,
                    'args' => ['input' => ['type' => $someInputObject]],
                ],
            ],
        ]);

        $schema = GraphQLSchema([
            'query'        => $this->blogQuery,
            'mutation'     => $someMutation,
            'subscription' => $someSubscription,
        ]);

        $this->assertEquals($nestedInputObject, $schema->build()->getTypeMap()[$nestedInputObject->getName()]);
    }

    /**
     * @throws \Exception
     */
    public function testIncludesInterfacePossibleTypesInSchemaTypeMap()
    {
        $someInterface = GraphQLInterfaceType([
            'name'   => 'SomeInterface',
            'fields' => [
                'f' => ['type' => GraphQLInt()],
            ],
        ]);

        $someSubtype = GraphQLObjectType([
            'name'       => 'SomeSubtype',
            'fields'     => [
                'f' => ['type' => GraphQLInt()],
            ],
            'interfaces' => [$someInterface],
        ]);

        $schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'iface' => ['type' => $someInterface],
                ],
            ]),
            'types' => [$someSubtype],
        ]);

        $this->assertEquals($someSubtype, $schema->build()->getTypeMap()[$someSubtype->getName()]);
    }

    /**
     * @throws \Exception
     * @throws \TypeError
     */
    public function testStringifySimpleTypes()
    {
        $this->assertEquals(TypeEnum::INT, (string)GraphQLInt());
        $this->assertEquals('Article', (string)$this->blogArticle);
        $this->assertEquals('Interface', (string)GraphQLInterfaceType());
        $this->assertEquals('Union', (string)GraphQLUnionType());
        $this->assertEquals('Enum', (string)GraphQLEnumType());
        $this->assertEquals(TypeEnum::INT, (string)GraphQLInt());
        $this->assertEquals('Int!', (string)GraphQLNonNull(GraphQLInt()));
        $this->assertEquals('[Int]!', (string)GraphQLNonNull(GraphQLList(GraphQLInt())));
        $this->assertEquals('[Int!]', (string)GraphQLList(GraphQLNonNull(GraphQLInt())));
        $this->assertEquals('[[Int]]', (string)GraphQLList(GraphQLList(GraphQLInt())));
    }

    /**
     * @param $type
     * @param $answer
     * @throws \Exception
     * @throws \TypeError
     * @dataProvider identifiesInputTypesDataProvider
     */
    public function testIdentifiesInputTypes($type, $answer)
    {
        $this->assertEquals($answer, isOutputType($type));
        $this->assertEquals($answer, isOutputType(GraphQLList($type)));
        $this->assertEquals($answer, isOutputType(GraphQLNonNull($type)));
    }

    public function identifiesInputTypesDataProvider(): array
    {
        // We cannot use the class fields here because they do not get instantiated for data providers.
        return [
            [GraphQLInt(), true],
            [GraphQLObjectType(['name' => 'Object']), true],
            [GraphQLInterfaceType(), true],
            [GraphQLUnionType(), true],
            [GraphQLEnumType(), true],
            [GraphQLInputObjectType(), false],
        ];
    }

    /**
     * @expectedException \TypeError
     */
    public function testProhibitsNestingNonNullInsideNonNull()
    {
        GraphQLNonNull(GraphQLNonNull(GraphQLInt()));

        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Exception
     */
    public function testAllowsAThunkForUnionMemberTypes()
    {
        $union = GraphQLUnionType([
            'name'  => 'ThunkUnion',
            'types' => function () {
                return [$this->objectType];
            }
        ]);

        $types = $union->getTypes();

        $this->assertEquals(1, count($types));
        $this->assertEquals($this->objectType, $types[0]);
    }

    /**
     * @throws \Exception
     */
    public function testDoesNotMutatePassedFieldDefinitions()
    {
        $fields = [
            'field1' => ['type' => GraphQLString()],
            'field2' => [
                'type' => GraphQLString(),
                'args' => [
                    'id' => ['type' => GraphQLString()],
                ],
            ],
        ];

        $testObject1 = GraphQLObjectType([
            'name'   => 'Test1',
            'fields' => $fields,
        ]);

        $testObject2 = GraphQLObjectType([
            'name'   => 'Test2',
            'fields' => $fields,
        ]);

        $this->assertEquals($testObject2->getFields(), $testObject1->getFields());

        $testInputObject1 = GraphQLInputObjectType([
            'name'   => 'Test1',
            'fields' => $fields,
        ]);

        $testInputObject2 = GraphQLInputObjectType([
            'name'   => 'Test2',
            'fields' => $fields,
        ]);

        $this->assertEquals($testInputObject2->getFields(), $testInputObject1->getFields());

        $this->assertInstanceOf(StringType::class, $fields['field1']['type']);
        $this->assertInstanceOf(StringType::class, $fields['field2']['type']);
        $this->assertInstanceOf(StringType::class, $fields['field2']['args']['id']['type']);
    }

    // TODO: Assess if we want to test "accepts an Object type with a field function".

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnObjectTypeFieldWithNullConfig()
    {
        $objType = GraphQLObjectType([
            'name'   => 'SomeObject',
            'fields' => ['f' => null],
        ]);

        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    function testRejectsAnObjectTypeWithIncorrectlyTypedFields()
    {
        $objType = GraphQLObjectType([
            'name'   => 'SomeObject',
            'fields' => [['f' => null]],
        ]);

        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnObjectTypeWithAFieldFunctionThatReturnsIncorrectType()
    {
        $objType = GraphQLObjectType([
            'name'   => 'SomeObject',
            'fields' => function () {
                return [['f' => null]];
            },
        ]);

        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    // Field arg config must be object

    /**
     * @throws \Exception
     */
    public function testAcceptsAnObjectTypeWithFieldArgs()
    {
        $objType = GraphQLObjectType([
            'name'   => 'SomeObject',
            'fields' => [
                'goodField' => [
                    'type' => GraphQLString(),
                    'args' => [
                        'goodArg' => ['type' => GraphQLString()],
                    ],
                ],
            ],
        ]);

        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Exception
     * @expectedException \Exception
     */
    public function testRejectsAnObjectTypeWithIncorrectlyTypedFieldArgs()
    {
        $objType = GraphQLObjectType([
            'name'   => 'SomeObject',
            'fields' => [
                'badField' => [
                    'type' => GraphQLString(),
                    'args' => [['badArg' => GraphQLString()]],
                ],
            ],
        ]);

        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testDoesNotAllowIsDeprecatedWithoutDeprecationReasonOnField()
    {
        $oldObject = GraphQLObjectType([
            'name'   => 'OldObject',
            'fields' => [
                'field' => [
                    'type'         => GraphQLString(),
                    'isDeprecated' => true,
                ],
            ],
        ]);

        $this->schemaWithField($oldObject);

        $this->addToAssertionCount(1);
    }

    // Object interfaces must be array

    /**
     * @throws \Exception
     */
    public function testAcceptsAnObjectTypeWithArrayInterfaces()
    {
        $objType = GraphQLObjectType([
            'name'       => 'SomeObject',
            'interfaces' => [$this->interfaceType],
            'fields'     => ['f' => ['type' => GraphQLString()]],
        ]);

        $this->assertEquals($this->interfaceType, $objType->getInterfaces()[0]);
    }

    /**
     * @throws \Exception
     */
    public function testAcceptsAnObjectTypeWithInterfacesAsAFunctionReturningAnArray()
    {
        $objType = GraphQLObjectType([
            'name'       => 'SomeObject',
            'interfaces' => [$this->interfaceType],
            'fields'     => [
                'f' => function () {
                    return ['type' => GraphQLString()];
                },
            ],
        ]);

        $this->assertEquals($this->interfaceType, $objType->getInterfaces()[0]);
    }

    // TODO: Assess if we want to test "rejects an Object type with incorrectly typed interfaces".

    // TODO: Assess if we want to test "rejects an Object type with interfaces as a function returning an incorrect type".

    // Type System: Object fields must have valid resolve values

    /**
     * @throws \Exception
     */
    public function testAcceptsALambdaAsAnObjectFieldResolver()
    {
        $this->schemaWithObjectWithFieldResolver(function () {
            return [];
        });

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \TypeError
     * @throws \Exception
     */
    public function testRejectsAnEmptyArrayFieldResolver()
    {
        $this->schemaWithObjectWithFieldResolver([]);

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \TypeError
     * @throws \Exception
     */
    public function testRejectsAnScalarFieldResolver()
    {
        $this->schemaWithObjectWithFieldResolver(0);

        $this->addToAssertionCount(1);
    }

    // Type System: Interface types must be resolvable

    /**
     * @throws \Exception
     */
    public function testAcceptsAnInterfaceTypeDefiningResolveType()
    {
        $anotherInterfaceType = GraphQLInterfaceType([
            'name'        => 'AnotherInterface',
            'fields'      => ['f' => ['type' => GraphQLString()]],
            'resolveType' => function () {
                return '';
            }
        ]);

        $this->schemaWithField(
            GraphQLObjectType([
                'name'       => 'SomeObject',
                'interfaces' => [$anotherInterfaceType],
                'fields'     => ['f' => ['type' => GraphQLString()]],
            ])
        );

        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Exception
     */
    public function testAcceptsAnInterfaceTypeWithImplementingTypeDefiningIsTypeOf()
    {
        $anotherInterfaceType = GraphQLInterfaceType([
            'name'   => 'AnotherInterface',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $this->schemaWithField(
            GraphQLObjectType([
                'name'       => 'SomeObject',
                'interfaces' => [$anotherInterfaceType],
                'fields'     => ['f' => ['type' => GraphQLString()]],
                'isTypeOf'   => function () {
                    return true;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Exception
     */
    public function testAcceptsAnInterfaceTypeDefiningResolveTypeWithImplementingTypeDefiningIsTypeOf()
    {
        $anotherInterfaceType = GraphQLInterfaceType([
            'name'        => 'AnotherInterface',
            'fields'      => ['f' => ['type' => GraphQLString()]],
            'resolveType' => function () {
                return '';
            }
        ]);

        $this->schemaWithField(
            GraphQLObjectType([
                'name'       => 'SomeObject',
                'interfaces' => [$anotherInterfaceType],
                'fields'     => ['f' => ['type' => GraphQLString()]],
                'isTypeOf'   => function () {
                    return true;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \TypeError
     */
    public function testRejectsAnInterfaceTypeWithAnIncorrectTypeForResolveType1()
    {
        GraphQLInterfaceType([
            'name'        => 'AnotherInterface',
            'resolveType' => [],
            'fields'      => ['f' => ['type' => GraphQLString()]],
        ]);
    }

    // Type System: Union types must be resolvable

    /**
     * @throws \Exception
     */
    public function testAcceptsUnionTypeDefiningResolveType()
    {
        $this->schemaWithField(
            GraphQLUnionType([
                'name'  => 'SomeUnion',
                'types' => [$this->objectType],
            ])
        );

        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Exception
     */
    public function testAcceptsAUnionOfObjectTypesDefiningIsTypeOf()
    {
        $objectWithIsTypeOf = GraphQLObjectType([
            'name'   => 'ObjectWithIsTypeOf',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $this->schemaWithField(
            GraphQLUnionType([
                'name'  => 'SomeUnion',
                'types' => [$objectWithIsTypeOf],
            ])
        );

        $this->addToAssertionCount(1);
    }

    // TODO: Assess if we want to test "accepts a Union type defining resolveType of Object types defining isTypeOf".

    /**
     * @throws \Exception
     * @expectedException \TypeError
     */
    public function testRejectsAnInterfaceTypeWithAnIncorrectTypeForResolveType2()
    {
        $objectWithIsTypeOf = GraphQLObjectType([
            'name'   => 'ObjectWithIsTypeOf',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $this->schemaWithField(
            GraphQLUnionType([
                'name'        => 'SomeUnion',
                'resolveType' => [],
                'types'       => [$objectWithIsTypeOf],
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Scalar types must be serializable

    /**
     * @throws \Exception
     */
    public function testAcceptsAScalarTypeDefiningSerialize()
    {
        $this->schemaWithField(
            GraphQLScalarType([
                'name'      => 'SomeScalar',
                'serialize' => function () {
                    return null;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    /**
     * @throws \Exception
     * @expectedException \Exception
     */
    public function testRejectsAScalarTypeNotDefiningSerialize()
    {
        $this->schemaWithField(
            GraphQLScalarType([
                'name' => 'SomeScalar',
            ])
        );

        $this->addToAssertionCount(1);
    }
}
