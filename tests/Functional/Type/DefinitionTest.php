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
        $this->enumType        = GraphQLUnionType(['name' => 'Enum', 'values' => ['foo' => []]]);
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
        return [
            [GraphQLInt(), true],
            [GraphQLObjectType(), true],
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
    }
}
