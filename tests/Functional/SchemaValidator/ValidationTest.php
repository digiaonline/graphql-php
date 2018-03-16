<?php

namespace Digia\GraphQL\Test\Functional\SchemaValidator;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\SchemaValidator\SchemaValidatorInterface;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\buildSchema;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Language\locationShorthandToArray;
use function Digia\GraphQL\Language\locationsShorthandToArray;
use function Digia\GraphQL\Type\GraphQLEnumType;
use function Digia\GraphQL\Type\GraphQLInputObjectType;
use function Digia\GraphQL\Type\GraphQLInterfaceType;
use function Digia\GraphQL\Type\GraphQLList;
use function Digia\GraphQL\Type\GraphQLNonNull;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLScalarType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\GraphQLUnionType;

function withModifiers($types)
{
    return \array_merge(
        $types,
        array_map(function ($type) {
            return GraphQLList($type);
        }, $types),
        array_map(function ($type) {
            return GraphQLNonNull($type);
        }, $types),
        array_map(function ($type) {
            return GraphQLNonNull(GraphQLList($type));
        }, $types)
    );
}

function schemaWithFieldType($type)
{
    return GraphQLSchema([
        'query' => GraphQLObjectType([
            'name'   => 'Query',
            'fields' => ['f' => ['type' => $type]]
        ]),
        'types' => [$type],
    ]);
}

class ValidationTest extends TestCase
{
    /**
     * @var SchemaValidatorInterface
     */
    protected $schemaValidator;

    protected $someScalarType;
    protected $someObjectType;
    protected $someUnionType;
    protected $someInterfaceType;
    protected $someEnumType;
    protected $someInputObjectType;
    protected $outputTypes;
    protected $noOutputTypes;
    protected $inputTypes;
    protected $noInputTypes;

    public function setUp()
    {
        $this->schemaValidator = GraphQL::get(SchemaValidatorInterface::class);

        $this->someScalarType = GraphQLScalarType([
            'name'         => 'SomeScalar',
            'serialize'    => function () {
            },
            'parseValue'   => function () {
            },
            'parseLiteral' => function () {
            },
        ]);

        $this->someObjectType = GraphQLObjectType([
            'name'   => 'SomeObject',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $this->someUnionType = GraphQLUnionType([
            'name'  => 'SomeUnion',
            'types' => [$this->someObjectType],
        ]);

        $this->someInterfaceType = GraphQLInterfaceType([
            'name'   => 'SomeInterface',
            'fields' => ['f' => ['type' => GraphQLString()]],
        ]);

        $this->someEnumType = GraphQLEnumType([
            'name'   => 'SomeEnum',
            'values' => ['ONLY' => []],
        ]);

        $this->someInputObjectType = GraphQLInputObjectType([
            'name'   => 'SomeInputObject',
            'fields' => [
                'val' => ['type' => GraphQLString(), 'defaultValue' => 'hello'],
            ],
        ]);

        $this->outputTypes = withModifiers([
            GraphQLString(),
            $this->someScalarType,
            $this->someEnumType,
            $this->someObjectType,
            $this->someUnionType,
            $this->someInterfaceType,
        ]);

        $this->noOutputTypes = withModifiers([$this->someInputObjectType]);

        $this->inputTypes = withModifiers([
            GraphQLString(),
            $this->someScalarType,
            $this->someEnumType,
            $this->someInputObjectType,
        ]);

        $this->noInputTypes = withModifiers([
            $this->someObjectType,
            $this->someUnionType,
            $this->someInterfaceType,
        ]);
    }

    // Type System: A Schema must have Object root types

    // accepts a Schema whose query type is an object type

    public function testAcceptsASchemaWhoseQueryTypeIsAnObjectType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: String
        }
        '));

        $this->expectValid($schema);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schemaWithDef = buildSchema(dedent('
        schema {
          query: QueryRoot
        }
        
        type QueryRoot {
          test: String
        }
        '));

        $this->expectValid($schemaWithDef);
    }

    // accepts a Schema whose query and mutation types are object types

    public function testAcceptsASchemaWhoseQueryAndMutationTypesAreObjectTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: String
        }
        
        type Mutation {
          test: String
        }
        '));

        $this->expectValid($schema);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schemaWithDef = buildSchema(dedent('
        schema {
          query: QueryRoot
        }
        
        type QueryRoot {
          test: String
        }
        
        type MutationRoot {
          test: String
        }
        '));

        $this->expectValid($schemaWithDef);
    }

    // accepts a Schema whose query and subscription types are object types

    public function testAcceptsASchemaWhoseQueryAndSubscriptionTypesAreObjectTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: String
        }
        
        type Subscription {
          test: String
        }
        '));

        $this->expectValid($schema);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schemaWithDef = buildSchema(dedent('
        schema {
          query: QueryRoot
        }
        
        type QueryRoot {
          test: String
        }
        
        type SubscriptionRoot {
          test: String
        }
        '));

        $this->expectValid($schemaWithDef);
    }

    // rejects a Schema without a query type

    public function testRejectsASchemaWithoutAQueryType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Mutation {
          test: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Query root type must be provided.',
                'locations' => null,
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schemaWithDef = buildSchema(dedent('
        schema {
          mutation: MutationRoot
        }
        
        type MutationRoot {
          test: String
        }
        '));

        $this->expectInvalid($schemaWithDef, [
            [
                'message'   => 'Query root type must be provided.',
                'locations' => [locationShorthandToArray([1, 1])],
            ]
        ]);
    }

    // rejects a Schema whose query root type is not an Object type

    public function testRejectsASchemaWhoseQueryRootTypeIsNotAnObjectType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        input Query {
          test: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Query root type must be Object type, it cannot be Query.',
                'locations' => [locationShorthandToArray([1, 1])],
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schemaWithDef = buildSchema(dedent('
        schema {
          query: SomeInputObject
        }
        
        input SomeInputObject {
          test: String
        }
        '));

        $this->expectInvalid($schemaWithDef, [
            [
                'message'   => 'Query root type must be Object type, it cannot be SomeInputObject.',
                'locations' => [locationShorthandToArray([2, 10])],
            ]
        ]);
    }

    // rejects a Schema whose mutation type is an input type

    public function testRejectsASchemaWhoseMutationTypeIsAnInputType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          field: String
        }
        
        input Mutation {
          test: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Mutation root type must be Object type if provided, it cannot be Mutation.',
                'locations' => [locationShorthandToArray([5, 1])]
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schemaWithDef = buildSchema(dedent('
        schema {
          query: Query
          mutation: SomeInputObject
        }
        
        type Query {
          field: String
        }
        
        input SomeInputObject {
          test: String
        }
        '));

        $this->expectInvalid($schemaWithDef, [
            [
                'message'   => 'Mutation root type must be Object type if provided, it cannot be SomeInputObject.',
                'locations' => [locationShorthandToArray([3, 13])],
            ]
        ]);
    }

    // rejects a Schema whose subscription type is an input type

    public function testRejectsASchemaWhoseSubscriptionTypeIsAnInputType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          field: String
        }
        
        input Subscription {
          test: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Subscription root type must be Object type if provided, it cannot be Subscription.',
                'locations' => [locationShorthandToArray([5, 1])],
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schemaWithDef = buildSchema(dedent('
        schema {
          query: Query
          subscription: SomeInputObject
        }
        
        type Query {
          field: String
        }
        
        input SomeInputObject {
          test: String
        }
        '));

        $this->expectInvalid($schemaWithDef, [
            [
                'message'   => 'Subscription root type must be Object type if provided, it cannot be SomeInputObject.',
                'locations' => [locationShorthandToArray([3, 17])],
            ]
        ]);
    }

    // rejects a Schema whose directives are incorrectly typed

    public function testRejectsASchemaWhoseDirectivesAreIncorrectlyTypes()
    {
        $schema = GraphQLSchema([
            'query'      => $this->someObjectType,
            'directives' => ['somedirective']
        ]);

        $this->expectInvalid($schema, [
            [
                'message' => 'Expected directive but got: somedirective.',
            ]
        ]);
    }

    // Type System: Objects must have fields

    // accepts an Object type with fields object

    public function testAcceptsAnObjectTypeWithFieldsObject()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          field: SomeObject
        }
        
        type SomeObject {
          field: String
        }
        '));

        $this->expectValid($schema);
    }

    // rejects an Object type with missing fields

    public function testRejectsAnObjectTypeWithMissingFields()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: IncompleteObject
        }
        
        type IncompleteObject
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Type IncompleteObject must define one or more fields.',
                'locations' => [],
            ]
        ]);

        $manualSchema = schemaWithFieldType(
            GraphQLObjectType([
                'name'   => 'IncompleteObject',
                'fields' => [],
            ])
        );

        $this->expectInvalid($manualSchema, [
            [
                'message' => 'Type IncompleteObject must define one or more fields.',
            ]
        ]);

        $manualSchema2 = schemaWithFieldType(
            GraphQLObjectType([
                'name'   => 'IncompleteObject',
                'fields' => function () {
                    return [];
                },
            ])
        );

        $this->expectInvalid($manualSchema2, [
            [
                'message' => 'Type IncompleteObject must define one or more fields.',
            ]
        ]);
    }

    // rejects an Object type with incorrectly named fields

    public function testRejectsAnObjectTypeWithIncorrectlyNamedFields()
    {
        $schema = schemaWithFieldType(
            GraphQLObjectType([
                'name'   => 'SomeObject',
                'fields' => [
                    'bad-name-with-dashes' => ['type' => GraphQLString()],
                ],
            ])
        );

        $this->expectInvalid($schema, [
            [
                'message' => 'Names must match /^[_a-zA-Z][_a-zA-Z0-9]*$/ but "bad-name-with-dashes" does not.',
            ]
        ]);
    }

    // Skip: accepts an Object type with explicitly allowed legacy named fields

    // Skip: throws with bad value for explicitly allowed legacy names

    // Type System: Fields args must be properly named

    // accepts field args with valid names

    public function testAcceptsFieldArgumentsWithValidNames()
    {
        $schema = schemaWithFieldType(
            GraphQLObjectType([
                'name'   => 'SomeObject',
                'fields' => [
                    'goodField' => [
                        'type' => GraphQLString(),
                        'args' => [
                            'goodArg' => ['type' => GraphQLString()],
                        ],
                    ],
                ],
            ])
        );

        $this->expectValid($schema);
    }

    // rejects field arg with invalid names

    public function testRejectsFieldArgumentsWithInvalidNames()
    {
        $schema = schemaWithFieldType(
            GraphQLObjectType([
                'name'   => 'SomeObject',
                'fields' => [
                    'badField' => [
                        'type' => GraphQLString(),
                        'args' => [
                            'bad-name-with-dashes' => ['type' => GraphQLString()],
                        ],
                    ],
                ],
            ])
        );

        $this->expectInvalid($schema, [
            [
                'message' => 'Names must match /^[_a-zA-Z][_a-zA-Z0-9]*$/ but "bad-name-with-dashes" does not.',
            ]
        ]);
    }

    // Type System: Union types must be valid

    // accepts a Union type with member types

    public function testAcceptsAUnionTypeWithMemberTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: GoodUnion
        }
        
        type TypeA {
          field: String
        }
        
        type TypeB {
          field: String
        }
        
        union GoodUnion =
          | TypeA
          | TypeB
        '));

        $this->expectValid($schema);
    }

    // rejects a Union type with empty types

    public function testRejectsAUnionTypeWithEmptyTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: BadUnion
        }
        
        union BadUnion
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Union type BadUnion must define one or more member types.',
                'locations' => [locationShorthandToArray([5, 1])]
            ]
        ]);
    }

    // rejects a Union type with duplicated member type

    public function testRejectsAUnionTypeWithDuplicatedMemberTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: BadUnion
        }
        
        type TypeA {
          field: String
        }
        
        type TypeB {
          field: String
        }
        
        union BadUnion =
          | TypeA
          | TypeB
          | TypeA
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Union type BadUnion can only include type TypeA once.',
                'locations' => locationsShorthandToArray([[14, 5], [16, 5]])
            ]
        ]);
    }

    // rejects a Union type with non-Object members types

    public function testRejectsAUnionTypeWithNonObjectMemberTypes()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: BadUnion
        }
        
        type TypeA {
          field: String
        }
        
        type TypeB {
          field: String
        }
        
        union BadUnion =
          | TypeA
          | String
          | TypeB
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Union type BadUnion can only include Object types, it cannot include String.',
                'locations' => [locationShorthandToArray([15, 5])],
            ]
        ]);

        $badUnionMemberTypes = [
            GraphQLString(),
            GraphQLNonNull($this->someObjectType),
            GraphQLList($this->someObjectType),
            $this->someInterfaceType,
            $this->someUnionType,
            $this->someEnumType,
            $this->someInputObjectType,
        ];

        foreach ($badUnionMemberTypes as $memberType) {
            $badSchema = schemaWithFieldType(
                GraphQLUnionType(['name' => 'BadUnion', 'types' => [$memberType]])
            );

            $this->expectInvalid($badSchema, [
                [
                    'message' => sprintf(
                        'Union type BadUnion can only include Object types, it cannot include %s.',
                        (string)$memberType
                    ),
                ]
            ]);
        }
    }

    // Type System: Input Objects must have fields

    // accepts an Input Object type with fields

    public function testAcceptsAnInputObjectTypeWithFields()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          field(arg: SomeInputObject): String
        }
        
        input SomeInputObject {
          field: String
        }
        '));

        $this->expectValid($schema);
    }

    // rejects an Input Object type with missing fields

    public function testRejectsAnInputObjectTypeWithMissingFields()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          field(arg: SomeInputObject): String
        }
        
        input SomeInputObject
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Input Object type SomeInputObject must define one or more fields.',
                'locations' => [locationShorthandToArray([5, 1])],
            ]
        ]);
    }

    // rejects an Input Object type with incorrectly typed fields

    public function testRejectsAnInputObjectTypeWithIncorrectlyTypedFields()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          field(arg: SomeInputObject): String
        }
        
        type SomeObject {
          field: String
        }
        
        union SomeUnion = SomeObject
        
        input SomeInputObject {
          badObject: SomeObject
          badUnion: SomeUnion
          goodInputObject: SomeInputObject
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'The type of SomeInputObject.badObject must be Input Type but got: SomeObject.',
                'locations' => [locationShorthandToArray([12, 3])],
            ],
            [
                'message'   => 'The type of SomeInputObject.badUnion must be Input Type but got: SomeUnion.',
                'locations' => [locationShorthandToArray([13, 3])],
            ]
        ]);
    }

    // Type System: Enum types must be well defined

    // rejects an Enum type without values

    public function testRejectsAnEnumTypeWithoutValues()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          field: SomeEnum
        }
        
        enum SomeEnum
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Enum type SomeEnum must define one or more values.',
                'locations' => [locationShorthandToArray([5, 1])],
            ]
        ]);
    }

    // rejects an Enum type with duplicate values

    public function testRejectsAnEnumTypeWithDuplicateValues()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          field: SomeEnum
        }
        
        enum SomeEnum {
          SOME_VALUE
          SOME_VALUE
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message'   => 'Enum type SomeEnum can include value SOME_VALUE only once.',
                'locations' => locationsShorthandToArray([[6, 3], [7, 3]]),
            ]
        ]);
    }

    // rejects an Enum type with incorrectly named values

    public function testRejectsAnEnumTypeWithIncorrectlyNamedValues()
    {
        $schemaWithEnum = function ($name) {
            return schemaWithFieldType(
                GraphQLEnumType([
                    'name'   => 'SomeEnum',
                    'values' => [
                        $name => [],
                    ],
                ])
            );
        };

        $badEnumValues = ['#value', '1value', 'KEBAB-CASE'];

        foreach ($badEnumValues as $enumValue) {
            $this->expectInvalid($schemaWithEnum($enumValue), [
                [
                    'message' => sprintf('Names must match /^[_a-zA-Z][_a-zA-Z0-9]*$/ but "%s" does not.', $enumValue),
                ]
            ]);
        }

        $forbiddenEnumValues = ['true', 'false', 'null'];

        foreach ($forbiddenEnumValues as $enumValue) {
            $this->expectInvalid($schemaWithEnum($enumValue), [
                [
                    'message' => sprintf('Enum type SomeEnum cannot include value: %s.', $enumValue),
                ]
            ]);
        }
    }

    // Type System: Object fields must have output types

    // TODO: Implement the rest of the test cases.

    protected function expectValid($schema)
    {
        $errors = $this->schemaValidator->validate($schema);
        $this->assertEquals([], $errors);
    }

    protected function expectInvalid($schema, $expectedErrors)
    {
        $errors = $this->schemaValidator->validate($schema);
        $this->assertArraySubset($expectedErrors, \array_map(function (ValidationException $error) {
            return $error->toArray();
        }, $errors));
    }
}
