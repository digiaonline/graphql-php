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
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\Type\GraphQLBoolean;
use function Digia\GraphQL\Type\GraphQLEnumType;
use function Digia\GraphQL\Type\GraphQLInputObjectType;
use function Digia\GraphQL\Type\GraphQLInt;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLScalarType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\GraphQLUnionType;
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
        ]);
    }

    /**
     * @param $resolveValue
     * @return Schema
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
        ]);
    }

    // Type System: Example

    public function testQuerySchema()
    {
        $schema = GraphQLSchema([
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
        $schema = GraphQLSchema([
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
        $schema = GraphQLSchema([
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

        $field = $typeWithDeprecatedField->getFields()['bar'];

        $this->assertEquals(GraphQLString(), $field->getType());
        $this->assertEquals('A terrible reason', $field->getDeprecationReason());
        $this->assertTrue($field->isDeprecated());
        $this->assertEquals('bar', $field->getName());
        $this->assertEmpty($field->getArguments());
    }

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

        $this->assertEquals($nestedInputObject, $schema->getTypeMap()[$nestedInputObject->getName()]);
    }

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

        $this->assertEquals($someSubtype, $schema->getTypeMap()[$someSubtype->getName()]);
    }

    public function testStringifySimpleTypes()
    {
        $this->assertEquals(TypeNameEnum::INT, (string)GraphQLInt());
        $this->assertEquals('Article', (string)$this->blogArticle);
        $this->assertEquals('Interface', (string)GraphQLInterfaceType(['name' => 'Interface']));
        $this->assertEquals('Union', (string)GraphQLUnionType(['name' => 'Union']));
        $this->assertEquals('Enum', (string)GraphQLEnumType(['name' => 'Enum']));
        $this->assertEquals(TypeNameEnum::INT, (string)GraphQLInt());
        $this->assertEquals('Int!', (string)GraphQLNonNull(GraphQLInt()));
        $this->assertEquals('[Int]!', (string)GraphQLNonNull(GraphQLList(GraphQLInt())));
        $this->assertEquals('[Int!]', (string)GraphQLList(GraphQLNonNull(GraphQLInt())));
        $this->assertEquals('[[Int]]', (string)GraphQLList(GraphQLList(GraphQLInt())));
    }

    /**
     * @param $type
     * @param $answer
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
            [GraphQLInterfaceType(['name' => 'Interface']), true],
            [GraphQLUnionType(['name' => 'Union']), true],
            [GraphQLEnumType(['name' => 'Enum']), true],
            [GraphQLInputObjectType(['name' => 'InputObjectType']), false],
        ];
    }

    /**
     * @expectedException \Digia\Graphql\Error\InvalidTypeException
     */
    public function testProhibitsNestingNonNullInsideNonNull()
    {
        GraphQLNonNull(GraphQLNonNull(GraphQLInt()));

        $this->addToAssertionCount(1);
    }

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

    public function testAcceptsAnObjectTypeWithArrayInterfaces()
    {
        $objType = GraphQLObjectType([
            'name'       => 'SomeObject',
            'interfaces' => [$this->interfaceType],
            'fields'     => ['f' => ['type' => GraphQLString()]],
        ]);

        $this->assertEquals($this->interfaceType, $objType->getInterfaces()[0]);
    }

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

    // Type System: Union types must be resolvable

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

    // Type System: Scalar types must be serializable

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

    public function testAcceptsAScalarTypeDefiningParseValueAndParseLiteral()
    {
        $this->schemaWithField(
            GraphQLScalarType([
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
            GraphQLScalarType([
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
            GraphQLScalarType([
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
            GraphQLObjectType([
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
            GraphQLUnionType([
                'name'  => 'AnotherObject',
                'types' => [$this->objectType],
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAnUnionTypeWithFunctionReturningAnArrayOfTypes()
    {
        $this->schemaWithField(
            GraphQLUnionType([
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
            GraphQLUnionType([
                'name' => 'AnotherObject',
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Input Objects must have fields

    public function testAcceptsAnInputObjectTypeWithFields()
    {
        $inputObjectType = GraphQLInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $field = $inputObjectType->getFields()['f'];
        $this->assertEquals(GraphQLString(), $field->getType());
    }

    public function testAcceptsAnInputObjectTypeWithAFieldFunction()
    {
        $inputObjectType = GraphQLInputObjectType([
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
        $inputObjectType = GraphQLInputObjectType([
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
        $enumType = GraphQLEnumType([
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
        $enumType = GraphQLEnumType([
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
        $enumType = GraphQLEnumType([
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
        $enumType = GraphQLEnumType([
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
        $enumType = GraphQLEnumType([
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
            GraphQLList(GraphQLString()),
            GraphQLNonNull(GraphQLString()),
        ];

        foreach ($types as $type) {
            GraphQLList($type);
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
            GraphQLList(GraphQLString()),
            GraphQLList(GraphQLNonNull(GraphQLString())),
        ];

        foreach ($types as $type) {
            GraphQLNonNull($type);
            $this->addToAssertionCount(1);
        }
    }

    /**
     * @expectedException \Digia\Graphql\Error\InvalidTypeException
     */
    public function testNonNullMustNotAcceptNonTypes()
    {
        $nonTypes = [GraphQLNonNull(GraphQLString()), [], '', null];

        foreach ($nonTypes as $nonType) {
            GraphQLNonNull($nonType);
            $this->addToAssertionCount(1);
        }
    }

    // Type System: A Schema must contain uniquely named types

    /**
     * @expectedException \Exception
     */
    public function testRejectsASchemaWhichDefinesABuiltInType()
    {
        $fakeString = GraphQLScalarType([
            'name'      => 'String',
            'serialize' => function () {
                return null;
            },
        ]);

        $queryType = GraphQLObjectType([
            'name'   => 'Query',
            'fields' => [
                'normal' => ['type' => GraphQLString()],
                'fake'   => ['type' => $fakeString],
            ]
        ]);

        GraphQLSchema(['query' => $queryType]);

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsASchemaWhichDefinesAnObjectTypeTwice()
    {
        $a = GraphQLObjectType([
            'name'   => 'SameName',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $b = GraphQLObjectType([
            'name'   => 'SameName',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $queryType = GraphQLObjectType([
            'name'   => 'Query',
            'fields' => [
                'a' => ['type' => $a],
                'b' => ['type' => $b],
            ]
        ]);

        GraphQLSchema(['query' => $queryType]);

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsASchemaWhichHaveSameNamedObjectsImplementingAnInterface()
    {
        $anotherInterface = GraphQLInterfaceType([
            'name'   => 'AnotherInterface',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $firstBadObject = GraphQLObjectType([
            'name'       => 'BadObject',
            'interfaces' => [$anotherInterface],
            'fields'     => ['f' => ['type' => GraphQLString()]],
        ]);

        $secondBadObject = GraphQLObjectType([
            'name'       => 'BadObject',
            'interfaces' => [$anotherInterface],
            'fields'     => ['f' => ['type' => GraphQLString()]],
        ]);

        $queryType = GraphQLObjectType([
            'name'   => 'Query',
            'fields' => [
                'iface' => ['type' => $anotherInterface],
            ]
        ]);

        GraphQLSchema([
            'query' => $queryType,
            'types' => [$firstBadObject, $secondBadObject],
        ]);

        $this->addToAssertionCount(1);
    }
}
