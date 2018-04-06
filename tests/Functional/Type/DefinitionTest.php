<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Schema\Schema;
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
use function Digia\GraphQL\Type\Boolean;
use function Digia\GraphQL\Type\Int;
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
use function Digia\GraphQL\Type\String;

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
        $this->blogImage = newObjectType([
            'name' => 'Image',
            'fields' => [
                'url' => ['type' => String()],
                'width' => ['type' => Int()],
                'height' => ['type' => Int()],
            ],
        ]);

        $this->blogAuthor = newObjectType([
            'name' => 'Author',
            'fields' => function () {
                return [
                    'id' => ['type' => String()],
                    'name' => ['type' => String()],
                    'pic' => [
                        'type' => $this->blogImage,
                        'args' => [
                            'width' => ['type' => Int()],
                            'height' => ['type' => Int()],
                        ],
                    ],
                    'recentArticle' => ['type' => $this->blogArticle],
                ];
            },
        ]);

        $this->blogArticle = newObjectType([
            'name' => 'Article',
            'fields' => [
                'id' => ['type' => String()],
                'isPublished' => ['type' => Boolean()],
                'author' => ['type' => $this->blogAuthor],
                'title' => ['type' => String()],
                'body' => ['type' => String()],
            ],
        ]);

        $this->blogQuery = newObjectType([
            'name' => 'Query',
            'fields' => [
                'article' => [
                    'args' => ['id' => ['type' => String()]],
                    'type' => $this->blogArticle,
                ],
                'feed' => [
                    'type' => newList($this->blogArticle),
                ],
            ],
        ]);

        $this->blogMutation = newObjectType([
            'name' => 'Mutation',
            'fields' => [
                'writeArticle' => [
                    'type' => $this->blogArticle,
                ],
            ],
        ]);

        $this->blogSubscription = newObjectType([
            'name' => 'Subscription',
            'fields' => [
                'articleSubscribe' => [
                    'args' => ['id' => ['type' => String()]],
                    'type' => $this->blogArticle,
                ],
            ],
        ]);

        $this->objectType = newObjectType(['name' => 'Object']);
        $this->interfaceType = newInterfaceType(['name' => 'Interface']);
        $this->unionType = newUnionType([
            'name' => 'Union',
            'types' => [$this->objectType],
        ]);
        $this->enumType = newEnumType([
            'name' => 'Enum',
            'values' => ['foo' => []],
        ]);
        $this->inputObjectType = newInputObjectType(['name' => 'InputObject']);
        $this->scalarType = newScalarType([
            'name' => 'Scalar',
            'serialize' => function () {
            },
            'parseValue' => function () {
            },
            'parseLiteral' => function () {
            },
        ]);
    }

    public function testQuerySchema()
    {
        $schema = newSchema([
            'query' => $this->blogQuery,
        ]);

        $this->assertEquals($this->blogQuery, $schema->getQueryType());

        $articleField = $this->blogQuery->getFields()['article'];

        $this->assertEquals($this->blogArticle, $articleField->getType());
        $this->assertEquals($this->blogArticle->getName(),
            $articleField->getType()->getName());
        $this->assertEquals('article', $articleField->getName());

        /** @var ObjectType $articleFieldType */
        $articleFieldType = $articleField->getType();

        $titleField = $articleFieldType->getFields()['title'];
        $this->assertEquals('title', $titleField->getName());
        $this->assertEquals(String(), $titleField->getType());
        $this->assertEquals('String', $titleField->getType()->getName());

        $authorField = $articleFieldType->getFields()['author'];

        /** @var ObjectType $authorFieldType */
        $authorFieldType = $authorField->getType();

        $recentArticleField = $authorFieldType->getFields()['recentArticle'];
        $this->assertEquals($this->blogArticle,
            !$recentArticleField ?: $recentArticleField->getType());

        $feedField = $this->blogQuery->getFields()['feed'];

        /** @var ListType $feedFieldType */
        $feedFieldType = $feedField->getType();
        $this->assertEquals($this->blogArticle, $feedFieldType->getOfType());

        $this->assertEquals('feed', $feedField->getName());
    }

    public function testMutationSchema()
    {
        $schema = newSchema([
            'mutation' => $this->blogMutation,
        ]);

        $this->assertEquals($this->blogMutation, $schema->getMutationType());

        $writeArticleField = $this->blogMutation->getFields()['writeArticle'];

        $this->assertEquals($this->blogArticle, $writeArticleField->getType());
        $this->assertEquals('Article',
            $writeArticleField->getType()->getName());
        $this->assertEquals('writeArticle', $writeArticleField->getName());
    }

    // Type System: Example

    public function testSubscriptionSchema()
    {
        $schema = newSchema([
            'subscription' => $this->blogSubscription,
        ]);

        $this->assertEquals($this->blogSubscription,
            $schema->getSubscriptionType());

        $articleSubscribeField = $this->blogSubscription->getFields()['articleSubscribe'];

        $this->assertEquals($this->blogArticle,
            $articleSubscribeField->getType());
        $this->assertEquals('Article',
            $articleSubscribeField->getType()->getName());
        $this->assertEquals('articleSubscribe',
            $articleSubscribeField->getName());
    }

    public function testEnumTypeWithDeprecatedValue()
    {
        $enumWithDeprecatedValue = newEnumType([
            'name' => 'EnumWithDeprecatedValue',
            'values' => ['foo' => ['deprecationReason' => 'Just because']],
        ]);

        /** @var EnumValue $enumValue */
        $enumValue = $enumWithDeprecatedValue->getValues()[0];

        $this->assertEquals('foo', $enumValue->getName());
        $this->assertTrue($enumValue->getIsDeprecated());
        $this->assertEquals('Just because', $enumValue->getDeprecationReason());
        $this->assertEquals('foo', $enumValue->getValue());
    }

    public function testObjectWithDeprecatedField()
    {
        $typeWithDeprecatedField = newObjectType([
            'name' => 'foo',
            'fields' => [
                'bar' => [
                    'type' => String(),
                    'deprecationReason' => 'A terrible reason',
                ],
            ],
        ]);

        $field = $typeWithDeprecatedField->getFields()['bar'];

        $this->assertEquals(String(), $field->getType());
        $this->assertEquals('A terrible reason',
            $field->getDeprecationReason());
        $this->assertTrue($field->getIsDeprecated());
        $this->assertEquals('bar', $field->getName());
        $this->assertEmpty($field->getArguments());
    }

    public function testIncludesNestedInputObjectsInSchemaTypeMap()
    {
        $nestedInputObject = newInputObjectType([
            'name' => 'NestedInputObject',
            'fields' => ['value' => ['type' => String()]],
        ]);

        $someInputObject = newInputObjectType([
            'name' => 'SomeInputObject',
            'fields' => ['nested' => ['type' => $nestedInputObject]],
        ]);

        $someMutation = newObjectType([
            'name' => 'SomeMutation',
            'fields' => [
                'mutateSomething' => [
                    'type' => $this->blogArticle,
                    'args' => ['input' => ['type' => $someInputObject]],
                ],
            ],
        ]);

        $someSubscription = newObjectType([
            'name' => 'SomeSubscription',
            'fields' => [
                'subscribeToSomething' => [
                    'type' => $this->blogArticle,
                    'args' => ['input' => ['type' => $someInputObject]],
                ],
            ],
        ]);

        $schema = newSchema([
            'query' => $this->blogQuery,
            'mutation' => $someMutation,
            'subscription' => $someSubscription,
        ]);

        $this->assertEquals($nestedInputObject,
            $schema->getTypeMap()[$nestedInputObject->getName()]);
    }

    // TODO: Assess if we want to test "defines an enum type with a value of `null` and `undefined`".

    public function testIncludesInterfacePossibleTypesInSchemaTypeMap()
    {
        $someInterface = newInterfaceType([
            'name' => 'SomeInterface',
            'fields' => [
                'f' => ['type' => Int()],
            ],
        ]);

        $someSubtype = newObjectType([
            'name' => 'SomeSubtype',
            'fields' => [
                'f' => ['type' => Int()],
            ],
            'interfaces' => [$someInterface],
        ]);

        $schema = newSchema([
            'query' => newObjectType([
                'name' => 'Query',
                'fields' => [
                    'iface' => ['type' => $someInterface],
                ],
            ]),
            'types' => [$someSubtype],
        ]);

        $this->assertEquals($someSubtype,
            $schema->getTypeMap()[$someSubtype->getName()]);
    }

    public function testStringifySimpleTypes()
    {
        $this->assertEquals(TypeNameEnum::INT, (string)Int());
        $this->assertEquals('Article', (string)$this->blogArticle);
        $this->assertEquals('Interface',
            (string)newInterfaceType(['name' => 'Interface']));
        $this->assertEquals('Union', (string)newUnionType(['name' => 'Union']));
        $this->assertEquals('Enum', (string)newEnumType(['name' => 'Enum']));
        $this->assertEquals(TypeNameEnum::INT, (string)Int());
        $this->assertEquals('Int!', (string)newNonNull(Int()));
        $this->assertEquals('[Int]!', (string)newNonNull(newList(Int())));
        $this->assertEquals('[Int!]', (string)newList(newNonNull(Int())));
        $this->assertEquals('[[Int]]', (string)newList(newList(Int())));
    }

    /**
     * @param $type
     * @param $answer
     *
     * @dataProvider identifiesInputTypesDataProvider
     */
    public function testIdentifiesInputTypes($type, $answer)
    {
        $this->assertEquals($answer, isOutputType($type));
        $this->assertEquals($answer, isOutputType(newList($type)));
        $this->assertEquals($answer, isOutputType(newNonNull($type)));
    }

    public function identifiesInputTypesDataProvider(): array
    {
        // We cannot use the class fields here because they do not get instantiated for data providers.
        return [
            [Int(), true],
            [newObjectType(['name' => 'Object']), true],
            [newInterfaceType(['name' => 'Interface']), true],
            [newUnionType(['name' => 'Union']), true],
            [newEnumType(['name' => 'Enum']), true],
            [newInputObjectType(['name' => 'InputObjectType']), false],
        ];
    }

    /**
     * @expectedException \Digia\Graphql\Error\InvalidTypeException
     */
    public function testProhibitsNestingNonNullInsideNonNull()
    {
        newNonNull(newNonNull(Int()));

        $this->addToAssertionCount(1);
    }

    public function testAllowsAThunkForUnionMemberTypes()
    {
        $union = newUnionType([
            'name' => 'ThunkUnion',
            'types' => function () {
                return [$this->objectType];
            },
        ]);

        $types = $union->getTypes();

        $this->assertEquals(1, count($types));
        $this->assertEquals($this->objectType, $types[0]);
    }

    public function testDoesNotMutatePassedFieldDefinitions()
    {
        $fields = [
            'field1' => ['type' => String()],
            'field2' => [
                'type' => String(),
                'args' => [
                    'id' => ['type' => String()],
                ],
            ],
        ];

        $testObject1 = newObjectType([
            'name' => 'Test1',
            'fields' => $fields,
        ]);

        $testObject2 = newObjectType([
            'name' => 'Test2',
            'fields' => $fields,
        ]);

        $this->assertEquals($testObject2->getFields(),
            $testObject1->getFields());

        $testInputObject1 = newInputObjectType([
            'name' => 'Test1',
            'fields' => $fields,
        ]);

        $testInputObject2 = newInputObjectType([
            'name' => 'Test2',
            'fields' => $fields,
        ]);

        $this->assertEquals($testInputObject2->getFields(),
            $testInputObject1->getFields());

        $this->assertEquals(String(), $fields['field1']['type']);
        $this->assertEquals(String(), $fields['field2']['type']);
        $this->assertEquals(String(), $fields['field2']['args']['id']['type']);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnObjectTypeFieldWithNullConfig()
    {
        $objType = newObjectType([
            'name' => 'SomeObject',
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
        $objType = newObjectType([
            'name' => 'SomeObject',
            'fields' => [['f' => null]],
        ]);

        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    // TODO: Assess if we want to test "accepts an Object type with a field function".

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnObjectTypeWithAFieldFunctionThatReturnsIncorrectType(
    )
    {
        $objType = newObjectType([
            'name' => 'SomeObject',
            'fields' => function () {
                return [['f' => null]];
            },
        ]);

        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAnObjectTypeWithFieldArgs()
    {
        $objType = newObjectType([
            'name' => 'SomeObject',
            'fields' => [
                'goodField' => [
                    'type' => String(),
                    'args' => [
                        'goodArg' => ['type' => String()],
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
        $objType = newObjectType([
            'name' => 'SomeObject',
            'fields' => [
                'badField' => [
                    'type' => String(),
                    'args' => [['badArg' => String()]],
                ],
            ],
        ]);

        $objType->getFields();

        $this->addToAssertionCount(1);
    }

    // Field arg config must be object

    /**
     * @expectedException \Exception
     */
    public function testDoesNotAllowIsDeprecatedWithoutDeprecationReasonOnField(
    )
    {
        $oldObject = newObjectType([
            'name' => 'OldObject',
            'fields' => [
                'field' => [
                    'type' => String(),
                    'isDeprecated' => true,
                ],
            ],
        ]);

        $this->schemaWithField($oldObject);

        $this->addToAssertionCount(1);
    }

    /**
     * @param TypeInterface $type
     *
     * @return Schema
     */
    protected function schemaWithField(TypeInterface $type): Schema
    {
        return newSchema([
            'query' => newObjectType([
                'name' => 'Query',
                'fields' => [
                    'field' => ['type' => $type],
                ],
            ]),
            'types' => [$type],
        ]);
    }

    public function testAcceptsAnObjectTypeWithArrayInterfaces()
    {
        $objType = newObjectType([
            'name' => 'SomeObject',
            'interfaces' => [$this->interfaceType],
            'fields' => ['f' => ['type' => String()]],
        ]);

        $this->assertEquals($this->interfaceType, $objType->getInterfaces()[0]);
    }

    // Object interfaces must be array

    public function testAcceptsAnObjectTypeWithInterfacesAsAFunctionReturningAnArray(
    )
    {
        $objType = newObjectType([
            'name' => 'SomeObject',
            'interfaces' => [$this->interfaceType],
            'fields' => [
                'f' => function () {
                    return ['type' => String()];
                },
            ],
        ]);

        $this->assertEquals($this->interfaceType, $objType->getInterfaces()[0]);
    }

    public function testAcceptsALambdaAsAnObjectFieldResolver()
    {
        $this->schemaWithObjectWithFieldResolver(function () {
            return [];
        });

        $this->addToAssertionCount(1);
    }

    // TODO: Assess if we want to test "rejects an Object type with incorrectly typed interfaces".

    // TODO: Assess if we want to test "rejects an Object type with interfaces as a function returning an incorrect type".

    // Type System: Object fields must have valid resolve values

    /**
     * @param $resolveValue
     *
     * @return Schema
     */
    protected function schemaWithObjectWithFieldResolver($resolveValue): Schema
    {
        $badResolverType = newObjectType([
            'name' => 'BadResolver',
            'fields' => [
                'badField' => [
                    'type' => String(),
                    'resolve' => $resolveValue,
                ],
            ],
        ]);

        return newSchema([
            'query' => newObjectType([
                'name' => 'Query',
                'fields' => [
                    'f' => ['type' => $badResolverType],
                ],
            ]),
        ]);
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
        $anotherInterfaceType = newInterfaceType([
            'name' => 'AnotherInterface',
            'fields' => ['f' => ['type' => String()]],
            'resolveType' => function () {
                return '';
            },
        ]);

        $this->schemaWithField(
            newObjectType([
                'name' => 'SomeObject',
                'interfaces' => [$anotherInterfaceType],
                'fields' => ['f' => ['type' => String()]],
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAnInterfaceTypeWithImplementingTypeDefiningIsTypeOf(
    )
    {
        $anotherInterfaceType = newInterfaceType([
            'name' => 'AnotherInterface',
            'fields' => ['f' => ['type' => String()]],
        ]);

        $this->schemaWithField(
            newObjectType([
                'name' => 'SomeObject',
                'interfaces' => [$anotherInterfaceType],
                'fields' => ['f' => ['type' => String()]],
                'isTypeOf' => function () {
                    return true;
                },
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAnInterfaceTypeDefiningResolveTypeWithImplementingTypeDefiningIsTypeOf(
    )
    {
        $anotherInterfaceType = newInterfaceType([
            'name' => 'AnotherInterface',
            'fields' => ['f' => ['type' => String()]],
            'resolveType' => function () {
                return '';
            },
        ]);

        $this->schemaWithField(
            newObjectType([
                'name' => 'SomeObject',
                'interfaces' => [$anotherInterfaceType],
                'fields' => ['f' => ['type' => String()]],
                'isTypeOf' => function () {
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
            newUnionType([
                'name' => 'SomeUnion',
                'types' => [$this->objectType],
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAUnionOfObjectTypesDefiningIsTypeOf()
    {
        $objectWithIsTypeOf = newObjectType([
            'name' => 'ObjectWithIsTypeOf',
            'fields' => ['f' => ['type' => String()]],
        ]);

        $this->schemaWithField(
            newUnionType([
                'name' => 'SomeUnion',
                'types' => [$objectWithIsTypeOf],
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Scalar types must be serializable

    public function testAcceptsAScalarTypeDefiningSerialize()
    {
        $this->schemaWithField(
            newScalarType([
                'name' => 'SomeScalar',
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
            newScalarType([
                'name' => 'SomeScalar',
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAScalarTypeDefiningParseValueAndParseLiteral()
    {
        $this->schemaWithField(
            newScalarType([
                'name' => 'SomeScalar',
                'serialize' => function () {
                    return null;
                },
                'parseValue' => function () {
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
            newScalarType([
                'name' => 'SomeScalar',
                'serialize' => function () {
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
            newScalarType([
                'name' => 'SomeScalar',
                'serialize' => function () {
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
            newObjectType([
                'name' => 'AnotherObject',
                'fields' => ['f' => ['type' => String()]],
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
            newUnionType([
                'name' => 'AnotherObject',
                'types' => [$this->objectType],
            ])
        );

        $this->addToAssertionCount(1);
    }

    public function testAcceptsAnUnionTypeWithFunctionReturningAnArrayOfTypes()
    {
        $this->schemaWithField(
            newUnionType([
                'name' => 'AnotherObject',
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
            newUnionType([
                'name' => 'AnotherObject',
            ])
        );

        $this->addToAssertionCount(1);
    }

    // Type System: Input Objects must have fields

    public function testAcceptsAnInputObjectTypeWithFields()
    {
        $inputObjectType = newInputObjectType([
            'name' => 'SomeInputObject',
            'fields' => ['f' => ['type' => String()]],
        ]);

        $field = $inputObjectType->getFields()['f'];
        $this->assertEquals(String(), $field->getType());
    }

    public function testAcceptsAnInputObjectTypeWithAFieldFunction()
    {
        $inputObjectType = newInputObjectType([
            'name' => 'SomeInputObject',
            'fields' => function () {
                return ['f' => ['type' => String()]];
            },
        ]);

        $field = $inputObjectType->getFields()['f'];
        $this->assertEquals(String(), $field->getType());
    }

    // Type System: Input Object fields must not have resolvers

    /**
     * @expectedException \Exception
     */
    public function testRejectsAnInputObjectTypeWithResolvers()
    {
        $inputObjectType = newInputObjectType([
            'name' => 'SomeInputObject',
            'fields' => [
                'f' => [
                    'type' => String(),
                    'resolve' => function () {
                        return 0;
                    },
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
        $enumType = newEnumType([
            'name' => 'SomeEnum',
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
        $enumType = newEnumType([
            'name' => 'SomeEnum',
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
        $enumType = newEnumType([
            'name' => 'SomeEnum',
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
        $enumType = newEnumType([
            'name' => 'SomeEnum',
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
        $enumType = newEnumType([
            'name' => 'SomeEnum',
            'values' => ['FOO' => ['isDeprecated' => true]],
        ]);

        $enumType->getValues();

        $this->addToAssertionCount(1);
    }

    // Type System: List must accept only types

    public function testListMustAcceptOnlyTypes()
    {
        $types = [
            String(),
            $this->scalarType,
            $this->objectType,
            $this->unionType,
            $this->interfaceType,
            $this->enumType,
            $this->inputObjectType,
            newList(String()),
            newNonNull(String()),
        ];

        foreach ($types as $type) {
            newList($type);
        }

        $this->addToAssertionCount(9);
    }

    // Type System: NonNull must only accept non-nullable types

    public function testNonNullMustAcceptOnlyNonNullableTypes()
    {
        $types = [
            String(),
            $this->scalarType,
            $this->objectType,
            $this->unionType,
            $this->interfaceType,
            $this->enumType,
            $this->inputObjectType,
            newList(String()),
            newList(newNonNull(String())),
        ];

        foreach ($types as $type) {
            newNonNull($type);
            $this->addToAssertionCount(1);
        }
    }

    /**
     * @expectedException \Digia\Graphql\Error\InvalidTypeException
     */
    public function testNonNullMustNotAcceptNonTypes()
    {
        $nonTypes = [newNonNull(String()), [], '', null];

        foreach ($nonTypes as $nonType) {
            newNonNull($nonType);
            $this->addToAssertionCount(1);
        }
    }

    // Type System: A Schema must contain uniquely named types

    /**
     * @expectedException \Exception
     */
    public function testRejectsASchemaWhichDefinesABuiltInType()
    {
        $fakeString = newScalarType([
            'name' => 'String',
            'serialize' => function () {
                return null;
            },
        ]);

        $queryType = newObjectType([
            'name' => 'Query',
            'fields' => [
                'normal' => ['type' => String()],
                'fake' => ['type' => $fakeString],
            ],
        ]);

        newSchema(['query' => $queryType]);

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsASchemaWhichDefinesAnObjectTypeTwice()
    {
        $a = newObjectType([
            'name' => 'SameName',
            'fields' => ['f' => ['type' => String()]],
        ]);

        $b = newObjectType([
            'name' => 'SameName',
            'fields' => ['f' => ['type' => String()]],
        ]);

        $queryType = newObjectType([
            'name' => 'Query',
            'fields' => [
                'a' => ['type' => $a],
                'b' => ['type' => $b],
            ],
        ]);

        newSchema(['query' => $queryType]);

        $this->addToAssertionCount(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testRejectsASchemaWhichHaveSameNamedObjectsImplementingAnInterface(
    )
    {
        $anotherInterface = newInterfaceType([
            'name' => 'AnotherInterface',
            'fields' => ['f' => ['type' => String()]],
        ]);

        $firstBadObject = newObjectType([
            'name' => 'BadObject',
            'interfaces' => [$anotherInterface],
            'fields' => ['f' => ['type' => String()]],
        ]);

        $secondBadObject = newObjectType([
            'name' => 'BadObject',
            'interfaces' => [$anotherInterface],
            'fields' => ['f' => ['type' => String()]],
        ]);

        $queryType = newObjectType([
            'name' => 'Query',
            'fields' => [
                'iface' => ['type' => $anotherInterface],
            ],
        ]);

        newSchema([
            'query' => $queryType,
            'types' => [$firstBadObject, $secondBadObject],
        ]);

        $this->addToAssertionCount(1);
    }
}
