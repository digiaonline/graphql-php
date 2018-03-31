<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Definition\EnumType;
use Digia\GraphQL\Type\Definition\EnumValue;
use Digia\GraphQL\Type\Definition\InputObjectType;
use Digia\GraphQL\Type\Definition\InterfaceType;
use Digia\GraphQL\Type\Definition\ListType;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Definition\ScalarType;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\TypeNameEnum;
use Digia\GraphQL\Type\Definition\UnionType;
use Digia\GraphQL\Schema\Schema;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\newGraphQLEnumType;
use function Digia\GraphQL\Type\newGraphQLInputObjectType;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\newGraphQLInterfaceType;
use function Digia\GraphQL\Type\newGraphQLList;
use function Digia\GraphQL\Type\newGraphQLNonNull;
use function Digia\GraphQL\Type\newGraphQLObjectType;
use function Digia\GraphQL\Type\newGraphQLScalarType;
use function Digia\GraphQL\Type\newGraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\newGraphQLUnionType;
use function Digia\GraphQL\Type\isOutputType;

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
        $this->blogImage = newGraphQLObjectType([
            'name'   => 'Image',
            'fields' => [
                'url'    => ['type' => GraphQLString()],
                'width'  => ['type' => GraphQLInt()],
                'height' => ['type' => GraphQLInt()],
            ],
        ]);

        $this->blogAuthor = newGraphQLObjectType([
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

        $this->blogArticle = newGraphQLObjectType([
            'name'   => 'Article',
            'fields' => [
                'id'          => ['type' => GraphQLString()],
                'isPublished' => ['type' => GraphQLBoolean()],
                'author'      => ['type' => $this->blogAuthor],
                'title'       => ['type' => GraphQLString()],
                'body'        => ['type' => GraphQLString()],
            ],
        ]);

        $this->blogQuery = newGraphQLObjectType([
            'name'   => 'Query',
            'fields' => [
                'article' => [
                    'args' => ['id' => ['type' => GraphQLString()]],
                    'type' => $this->blogArticle,
                ],
                'feed'    => [
                    'type' => newGraphQLList($this->blogArticle),
                ],
            ],
        ]);

        $this->blogMutation = newGraphQLObjectType([
            'name'   => 'Mutation',
            'fields' => [
                'writeArticle' => [
                    'type' => $this->blogArticle,
                ],
            ],
        ]);

        $this->blogSubscription = newGraphQLObjectType([
            'name'   => 'Subscription',
            'fields' => [
                'articleSubscribe' => [
                    'args' => ['id' => ['type' => GraphQLString()]],
                    'type' => $this->blogArticle,
                ],
            ],
        ]);

        $this->objectType      = newGraphQLObjectType(['name' => 'Object']);
        $this->interfaceType   = newGraphQLInterfaceType(['name' => 'Interface']);
        $this->unionType       = newGraphQLUnionType(['name' => 'Union', 'types' => [$this->objectType]]);
        $this->enumType        = newGraphQLEnumType(['name' => 'Enum', 'values' => ['foo' => []]]);
        $this->inputObjectType = newGraphQLInputObjectType(['name' => 'InputObject']);
        $this->scalarType      = newGraphQLScalarType([
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
     */
    protected function schemaWithField(TypeInterface $type): Schema
    {
        return newGraphQLSchema([
            'query' => newGraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'field' => ['type' => $type],
                ],
            ]),
            'types' => [$type],
        ]);
    }

    /**
     * @param $resolveValue
     * @return Schema
     */
    protected function schemaWithObjectWithFieldResolver($resolveValue): Schema
    {
        $badResolverType = newGraphQLObjectType([
            'name'   => 'BadResolver',
            'fields' => [
                'badField' => [
                    'type'    => GraphQLString(),
                    'resolve' => $resolveValue
                ],
            ],
        ]);

        return newGraphQLSchema([
            'query' => newGraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'f' => ['type' => $badResolverType],
                ],
            ])
        ]);
    }

    // Type System: Example

    public function testQuerySchema()
    {
        $schema = newGraphQLSchema([
            'query' => $this->blogQuery,
        ]);

        $this->assertEquals($this->blogQuery, $schema->getQueryType());

        $articleField = $this->blogQuery->getFields()['article'];

        $this->assertEquals($this->blogArticle, $articleField->getType());
        $this->assertEquals($this->blogArticle->getName(), $articleField->getType()->getName());
        $this->assertEquals('article', $articleField->getName());

        /** @var ObjectType $articleFieldType */
        $articleFieldType = $articleField->getType();

        $titleField = $articleFieldType->getFields()['title'];
        $this->assertEquals('title', $titleField->getName());
        $this->assertEquals(GraphQLString(), $titleField->getType());
        $this->assertEquals('String', $titleField->getType()->getName());

        $authorField = $articleFieldType->getFields()['author'];

        /** @var ObjectType $authorFieldType */
        $authorFieldType = $authorField->getType();

        $recentArticleField = $authorFieldType->getFields()['recentArticle'];
        $this->assertEquals($this->blogArticle, !$recentArticleField ?: $recentArticleField->getType());

        $feedField = $this->blogQuery->getFields()['feed'];

        /** @var ListType $feedFieldType */
        $feedFieldType = $feedField->getType();
        $this->assertEquals($this->blogArticle, $feedFieldType->getOfType());

        $this->assertEquals('feed', $feedField->getName());
    }

    public function testMutationSchema()
    {
        $schema = newGraphQLSchema([
            'mutation' => $this->blogMutation,
        ]);

        $this->assertEquals($this->blogMutation, $schema->getMutationType());

        $writeArticleField = $this->blogMutation->getFields()['writeArticle'];

        $this->assertEquals($this->blogArticle, $writeArticleField->getType());
        $this->assertEquals('Article', $writeArticleField->getType()->getName());
        $this->assertEquals('writeArticle', $writeArticleField->getName());
    }

    public function testSubscriptionSchema()
    {
        $schema = newGraphQLSchema([
            'subscription' => $this->blogSubscription,
        ]);

        $this->assertEquals($this->blogSubscription, $schema->getSubscriptionType());

        $articleSubscribeField = $this->blogSubscription->getFields()['articleSubscribe'];

        $this->assertEquals($this->blogArticle, $articleSubscribeField->getType());
        $this->assertEquals('Article', $articleSubscribeField->getType()->getName());
        $this->assertEquals('articleSubscribe', $articleSubscribeField->getName());
    }

    public function testEnumTypeWithDeprecatedValue()
    {
        $enumWithDeprecatedValue = newGraphQLEnumType([
            'name'   => 'EnumWithDeprecatedValue',
            'values' => ['foo' => ['deprecationReason' => 'Just because']],
        ]);

        /** @var EnumValue $enumValue */
        $enumValue = $enumWithDeprecatedValue->getValues()[0];

        $this->assertEquals('foo', $enumValue->getName());
        $this->assertTrue($enumValue->getIsDeprecated());
        $this->assertEquals('Just because', $enumValue->getDeprecationReason());
        $this->assertEquals('foo', $enumValue->getValue());
    }

    // TODO: Assess if we want to test "defines an enum type with a value of `null` and `undefined`".

    public function testObjectWithDeprecatedField()
    {
        $typeWithDeprecatedField = newGraphQLObjectType([
            'name'   => 'foo',
            'fields' => [
                'bar' => [
                    'type'              => GraphQLString(),
                    'deprecationReason' => 'A terrible reason',
                ],
            ],
        ]);

        $field = $typeWithDeprecatedField->getFields()['bar'];

        $this->assertEquals(GraphQLString(), $field->getType());
        $this->assertEquals('A terrible reason', $field->getDeprecationReason());
        $this->assertTrue($field->getIsDeprecated());
        $this->assertEquals('bar', $field->getName());
        $this->assertEmpty($field->getArguments());
    }

    public function testIncludesNestedInputObjectsInSchemaTypeMap()
    {
        $nestedInputObject = newGraphQLInputObjectType([
            'name'   => 'NestedInputObject',
            'fields' => ['value' => ['type' => GraphQLString()]],
        ]);

        $someInputObject = newGraphQLInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => ['nested' => ['type' => $nestedInputObject]],
        ]);

        $someMutation = newGraphQLObjectType([
            'name'   => 'SomeMutation',
            'fields' => [
                'mutateSomething' => [
                    'type' => $this->blogArticle,
                    'args' => ['input' => ['type' => $someInputObject]],
                ],
            ],
        ]);

        $someSubscription = newGraphQLObjectType([
            'name'   => 'SomeSubscription',
            'fields' => [
                'subscribeToSomething' => [
                    'type' => $this->blogArticle,
                    'args' => ['input' => ['type' => $someInputObject]],
                ],
            ],
        ]);

        $schema = newGraphQLSchema([
            'query'        => $this->blogQuery,
            'mutation'     => $someMutation,
            'subscription' => $someSubscription,
        ]);

        $this->assertEquals($nestedInputObject, $schema->getTypeMap()[$nestedInputObject->getName()]);
    }

    public function testIncludesInterfacePossibleTypesInSchemaTypeMap()
    {
        $someInterface = newGraphQLInterfaceType([
            'name'   => 'SomeInterface',
            'fields' => [
                'f' => ['type' => GraphQLInt()],
            ],
        ]);

        $someSubtype = newGraphQLObjectType([
            'name'       => 'SomeSubtype',
            'fields'     => [
                'f' => ['type' => GraphQLInt()],
            ],
            'interfaces' => [$someInterface],
        ]);

        $schema = newGraphQLSchema([
            'query' => newGraphQLObjectType([
                'name'   => 'Query',
                'fields' => [
                    'iface' => ['type' => $someInterface],
                ],
            ]),
            'types' => [$someSubtype],
        ]);

        $this->assertEquals($someSubtype, $schema->getTypeMap()[$someSubtype->getName()]);
    }

    public function testStringifySimpleTypes()
    {
        $this->assertEquals(TypeNameEnum::INT, (string)GraphQLInt());
        $this->assertEquals('Article', (string)$this->blogArticle);
        $this->assertEquals('Interface', (string)newGraphQLInterfaceType(['name' => 'Interface']));
        $this->assertEquals('Union', (string)newGraphQLUnionType(['name' => 'Union']));
        $this->assertEquals('Enum', (string)newGraphQLEnumType(['name' => 'Enum']));
        $this->assertEquals(TypeNameEnum::INT, (string)GraphQLInt());
        $this->assertEquals('Int!', (string)newGraphQLNonNull(GraphQLInt()));
        $this->assertEquals('[Int]!', (string)newGraphQLNonNull(newGraphQLList(GraphQLInt())));
        $this->assertEquals('[Int!]', (string)newGraphQLList(newGraphQLNonNull(GraphQLInt())));
        $this->assertEquals('[[Int]]', (string)newGraphQLList(newGraphQLList(GraphQLInt())));
    }

    /**
     * @param $type
     * @param $answer
     * @dataProvider identifiesInputTypesDataProvider
     */
    public function testIdentifiesInputTypes($type, $answer)
    {
        $this->assertEquals($answer, isOutputType($type));
        $this->assertEquals($answer, isOutputType(newGraphQLList($type)));
        $this->assertEquals($answer, isOutputType(newGraphQLNonNull($type)));
    }

    public function identifiesInputTypesDataProvider(): array
    {
        // We cannot use the class fields here because they do not get instantiated for data providers.
        return [
            [GraphQLInt(), true],
            [newGraphQLObjectType(['name' => 'Object']), true],
            [newGraphQLInterfaceType(['name' => 'Interface']), true],
            [newGraphQLUnionType(['name' => 'Union']), true],
            [newGraphQLEnumType(['name' => 'Enum']), true],
            [newGraphQLInputObjectType(['name' => 'InputObjectType']), false],
        ];
    }

    /**
     * @expectedException \Digia\Graphql\Error\InvalidTypeException
     */
    public function testProhibitsNestingNonNullInsideNonNull()
    {
        newGraphQLNonNull(newGraphQLNonNull(GraphQLInt()));

        $this->addToAssertionCount(1);
    }

    public function testAllowsAThunkForUnionMemberTypes()
    {
        $union = newGraphQLUnionType([
            'name'  => 'ThunkUnion',
            'types' => function () {
                return [$this->objectType];
            }
        ]);

        $types = $union->getTypes();

        $this->assertEquals(1, count($types));
        $this->assertEquals($this->objectType, $types[0]);
    }

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

        $testObject1 = newGraphQLObjectType([
            'name'   => 'Test1',
            'fields' => $fields,
        ]);

        $testObject2 = newGraphQLObjectType([
            'name'   => 'Test2',
            'fields' => $fields,
        ]);

        $this->assertEquals($testObject2->getFields(), $testObject1->getFields());

        $testInputObject1 = newGraphQLInputObjectType([
            'name'   => 'Test1',
            'fields' => $fields,
        ]);

        $testInputObject2 = newGraphQLInputObjectType([
            'name'   => 'Test2',
            'fields' => $fields,
        ]);

        $this->assertEquals($testInputObject2->getFields(), $testInputObject1->getFields());

        $this->assertEquals(GraphQLString(), $fields['field1']['type']);
        $this->assertEquals(GraphQLString(), $fields['field2']['type']);
        $this->assertEquals(GraphQLString(), $fields['field2']['args']['id']['type']);
    }

    // TODO: Assess if we want to test "accepts an Object type with a field function".

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnObjectTypeFieldWithNullConfig()
    {
        $objType = newGraphQLObjectType([
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
        $objType = newGraphQLObjectType([
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
        $objType = newGraphQLObjectType([
            'name'   => 'SomeObject',
            'fields' => function () {
                return [['f' => null]];
            },
        ]);

        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    // Field arg config must be object

    public function testAcceptsAnObjectTypeWithFieldArgs()
    {
        $objType = newGraphQLObjectType([
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
     * @expectedException \Exception
     */
    public function testRejectsAnObjectTypeWithIncorrectlyTypedFieldArgs()
    {
        $objType = newGraphQLObjectType([
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
        $oldObject = newGraphQLObjectType([
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

    public function testAcceptsAnObjectTypeWithArrayInterfaces()
    {
        $objType = newGraphQLObjectType([
            'name'       => 'SomeObject',
            'interfaces' => [$this->interfaceType],
            'fields'     => ['f' => ['type' => GraphQLString()]],
        ]);

        $this->assertEquals($this->interfaceType, $objType->getInterfaces()[0]);
    }

    public function testAcceptsAnObjectTypeWithInterfacesAsAFunctionReturningAnArray()
    {
        $objType = newGraphQLObjectType([
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

    public function testAcceptsALambdaAsAnObjectFieldResolver()
    {
        $this->schemaWithObjectWithFieldResolver(function () {
            return [];
        });

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnEmptyArrayFieldResolver()
    {
        $this->schemaWithObjectWithFieldResolver([]);

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnScalarFieldResolver()
    {
        $this->schemaWithObjectWithFieldResolver(0);

        $this->addToAssertionCount(1);
    }

    // Type System: Interface types must be resolvable

    public function testAcceptsAnInterfaceTypeDefiningResolveType()
    {
        $anotherInterfaceType = newGraphQLInterfaceType([
            'name'        => 'AnotherInterface',
            'fields'      => ['f' => ['type' => GraphQLString()]],
            'resolveType' => function () {
                return '';
            }
        ]);

        $this->schemaWithField(
            newGraphQLObjectType([
                'name'       => 'SomeObject',
                'interfaces' => [$anotherInterfaceType],
                'fields'     => ['f' => ['type' => GraphQLString()]],
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAnInterfaceTypeWithImplementingTypeDefiningIsTypeOf()
    {
        $anotherInterfaceType = newGraphQLInterfaceType([
            'name'   => 'AnotherInterface',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $this->schemaWithField(
            newGraphQLObjectType([
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

    public function testAcceptsAnInterfaceTypeDefiningResolveTypeWithImplementingTypeDefiningIsTypeOf()
    {
        $anotherInterfaceType = newGraphQLInterfaceType([
            'name'        => 'AnotherInterface',
            'fields'      => ['f' => ['type' => GraphQLString()]],
            'resolveType' => function () {
                return '';
            }
        ]);

        $this->schemaWithField(
            newGraphQLObjectType([
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

    // Type System: Union types must be resolvable

    public function testAcceptsUnionTypeDefiningResolveType()
    {
        $this->schemaWithField(
            newGraphQLUnionType([
                'name'  => 'SomeUnion',
                'types' => [$this->objectType],
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAUnionOfObjectTypesDefiningIsTypeOf()
    {
        $objectWithIsTypeOf = newGraphQLObjectType([
            'name'   => 'ObjectWithIsTypeOf',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $this->schemaWithField(
            newGraphQLUnionType([
                'name'  => 'SomeUnion',
                'types' => [$objectWithIsTypeOf],
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Scalar types must be serializable

    public function testAcceptsAScalarTypeDefiningSerialize()
    {
        $this->schemaWithField(
            newGraphQLScalarType([
                'name'      => 'SomeScalar',
                'serialize' => function () {
                    return null;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsAScalarTypeNotDefiningSerialize()
    {
        $this->schemaWithField(
            newGraphQLScalarType([
                'name' => 'SomeScalar',
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAScalarTypeDefiningParseValueAndParseLiteral()
    {
        $this->schemaWithField(
            newGraphQLScalarType([
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

    /**
     * @expectedException \Exception
     */
    public function testRejectsAScalarTypeDefiningParseValueButNotParseLiteral()
    {
        $this->schemaWithField(
            newGraphQLScalarType([
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

    /**
     * @expectedException \Exception
     */
    public function testRejectsAScalarTypeDefiningParseLiteralButNotParseValue()
    {
        $this->schemaWithField(
            newGraphQLScalarType([
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

    // Type System: Object types must be assertable

    public function testAcceptsAnObjectTypeWithAnIsTypeOfFunction()
    {
        $this->schemaWithField(
            newGraphQLObjectType([
                'name'     => 'AnotherObject',
                'fields'   => ['f' => ['type' => GraphQLString()]],
                'isTypeOf' => function () {
                    return true;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Union types must be array

    public function testAcceptsAnUnionTypeWithArrayTypes()
    {
        $this->schemaWithField(
            newGraphQLUnionType([
                'name'  => 'AnotherObject',
                'types' => [$this->objectType],
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAnUnionTypeWithFunctionReturningAnArrayOfTypes()
    {
        $this->schemaWithField(
            newGraphQLUnionType([
                'name'  => 'AnotherObject',
                'types' => function () {
                    return [$this->objectType];
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testRejectsAnUnionWithoutTypes()
    {
        $this->schemaWithField(
            newGraphQLUnionType([
                'name' => 'AnotherObject',
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Input Objects must have fields

    public function testAcceptsAnInputObjectTypeWithFields()
    {
        $inputObjectType = newGraphQLInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $field = $inputObjectType->getFields()['f'];
        $this->assertEquals(GraphQLString(), $field->getType());
    }

    public function testAcceptsAnInputObjectTypeWithAFieldFunction()
    {
        $inputObjectType = newGraphQLInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => function () {
                return ['f' => ['type' => GraphQLString()]];
            },
        ]);

        $field = $inputObjectType->getFields()['f'];
        $this->assertEquals(GraphQLString(), $field->getType());
    }

    // Type System: Input Object fields must not have resolvers

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnInputObjectTypeWithResolvers()
    {
        $inputObjectType = newGraphQLInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => [
                'f' => [
                    'type'    => GraphQLString(),
                    'resolve' => function () {
                        return 0;
                    }
                ],
            ],
        ]);

        $inputObjectType->getFields();

        $this->addToAssertionCount(1);
    }

    // TODO: Asses if we want to test "rejects an Input Object type with resolver constant".

    // Type System: Enum types must be well defined

    public function testAcceptsAWellDefinedEnumTypeWithEmptyValueDefinition()
    {
        $enumType = newGraphQLEnumType([
            'name'   => 'SomeEnum',
            'values' => [
                'FOO' => ['value' => 10],
                'BAR' => ['value' => 20],
            ],
        ]);

        $this->assertEquals(10, $enumType->getValue('FOO')->getValue());
        $this->assertEquals(20, $enumType->getValue('BAR')->getValue());
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnEnumWithIncorrectlyTypedValues()
    {
        $enumType = newGraphQLEnumType([
            'name'   => 'SomeEnum',
            'values' => ['FOO' => 10],
        ]);

        $enumType->getValues();

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnEnumTypeWithMissingValueDefinition()
    {
        $enumType = newGraphQLEnumType([
            'name'   => 'SomeEnum',
            'values' => ['FOO' => null],
        ]);

        $enumType->getValues();

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnEnumTypeWithIncorrectlyTypedValueDefinition()
    {
        $enumType = newGraphQLEnumType([
            'name'   => 'SomeEnum',
            'values' => ['FOO' => 10],
        ]);

        $enumType->getValues();

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testDoesNotAllowIsDeprecatedWithoutDeprecationReasonOnEnum()
    {
        $enumType = newGraphQLEnumType([
            'name'   => 'SomeEnum',
            'values' => ['FOO' => ['isDeprecated' => true]],
        ]);

        $enumType->getValues();

        $this->addToAssertionCount(1);
    }

    // Type System: List must accept only types

    public function testListMustAcceptOnlyTypes()
    {
        $types = [
            GraphQLString(),
            $this->scalarType,
            $this->objectType,
            $this->unionType,
            $this->interfaceType,
            $this->enumType,
            $this->inputObjectType,
            newGraphQLList(GraphQLString()),
            newGraphQLNonNull(GraphQLString()),
        ];

        foreach ($types as $type) {
            newGraphQLList($type);
        }

        $this->addToAssertionCount(9);
    }

    // Type System: NonNull must only accept non-nullable types

    public function testNonNullMustAcceptOnlyNonNullableTypes()
    {
        $types = [
            GraphQLString(),
            $this->scalarType,
            $this->objectType,
            $this->unionType,
            $this->interfaceType,
            $this->enumType,
            $this->inputObjectType,
            newGraphQLList(GraphQLString()),
            newGraphQLList(newGraphQLNonNull(GraphQLString())),
        ];

        foreach ($types as $type) {
            newGraphQLNonNull($type);
            $this->addToAssertionCount(1);
        }
    }

    /**
     * @expectedException \Digia\Graphql\Error\InvalidTypeException
     */
    public function testNonNullMustNotAcceptNonTypes()
    {
        $nonTypes = [newGraphQLNonNull(GraphQLString()), [], '', null];

        foreach ($nonTypes as $nonType) {
            newGraphQLNonNull($nonType);
            $this->addToAssertionCount(1);
        }
    }

    // Type System: A Schema must contain uniquely named types

    /**
     * @expectedException \Exception
     */
    public function testRejectsASchemaWhichDefinesABuiltInType()
    {
        $fakeString = newGraphQLScalarType([
            'name'      => 'String',
            'serialize' => function () {
                return null;
            },
        ]);

        $queryType = newGraphQLObjectType([
            'name'   => 'Query',
            'fields' => [
                'normal' => ['type' => GraphQLString()],
                'fake'   => ['type' => $fakeString],
            ]
        ]);

        newGraphQLSchema(['query' => $queryType]);

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsASchemaWhichDefinesAnObjectTypeTwice()
    {
        $a = newGraphQLObjectType([
            'name'   => 'SameName',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $b = newGraphQLObjectType([
            'name'   => 'SameName',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $queryType = newGraphQLObjectType([
            'name'   => 'Query',
            'fields' => [
                'a' => ['type' => $a],
                'b' => ['type' => $b],
            ]
        ]);

        newGraphQLSchema(['query' => $queryType]);

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsASchemaWhichHaveSameNamedObjectsImplementingAnInterface()
    {
        $anotherInterface = newGraphQLInterfaceType([
            'name'   => 'AnotherInterface',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $firstBadObject = newGraphQLObjectType([
            'name'       => 'BadObject',
            'interfaces' => [$anotherInterface],
            'fields'     => ['f' => ['type' => GraphQLString()]],
        ]);

        $secondBadObject = newGraphQLObjectType([
            'name'       => 'BadObject',
            'interfaces' => [$anotherInterface],
            'fields'     => ['f' => ['type' => GraphQLString()]],
        ]);

        $queryType = newGraphQLObjectType([
            'name'   => 'Query',
            'fields' => [
                'iface' => ['type' => $anotherInterface],
            ]
        ]);

        newGraphQLSchema([
            'query' => $queryType,
            'types' => [$firstBadObject, $secondBadObject],
        ]);

        $this->addToAssertionCount(1);
    }
}
