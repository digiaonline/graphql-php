<?php

namespace Digia\GraphQL\Test\Functional\Schema;

use Digia\GraphQL\Error\ValidationExceptionInterface;
use Digia\GraphQL\GraphQL;
use Digia\GraphQL\Schema\Validation\SchemaValidatorInterface;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\buildSchema;
use function Digia\GraphQL\extendSchema;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\Language\locationShorthandToArray;
use function Digia\GraphQL\Language\locationsShorthandToArray;
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
use function Digia\GraphQL\Util\toString;

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
        $this->schemaValidator = GraphQL::make(SchemaValidatorInterface::class);

        $this->someScalarType = newScalarType([
            'name' => 'SomeScalar',
            'serialize' => function () {
            },
            'parseValue' => function () {
            },
            'parseLiteral' => function () {
            },
        ]);

        $this->someObjectType = newObjectType([
            'name' => 'SomeObject',
            'fields' => ['f' => ['type' => String()]],
        ]);

        $this->someUnionType = newUnionType([
            'name' => 'SomeUnion',
            'types' => [$this->someObjectType],
        ]);

        $this->someInterfaceType = newInterfaceType([
            'name' => 'SomeInterface',
            'fields' => ['f' => ['type' => String()]],
        ]);

        $this->someEnumType = newEnumType([
            'name' => 'SomeEnum',
            'values' => ['ONLY' => []],
        ]);

        $this->someInputObjectType = newInputObjectType([
            'name' => 'SomeInputObject',
            'fields' => [
                'val' => ['type' => String(), 'defaultValue' => 'hello'],
            ],
        ]);

        $this->outputTypes = $this->withModifiers([
            String(),
            $this->someScalarType,
            $this->someEnumType,
            $this->someObjectType,
            $this->someUnionType,
            $this->someInterfaceType,
        ]);

        $this->noOutputTypes = $this->withModifiers([$this->someInputObjectType]);

        $this->inputTypes = $this->withModifiers([
            String(),
            $this->someScalarType,
            $this->someEnumType,
            $this->someInputObjectType,
        ]);

        $this->noInputTypes = $this->withModifiers([
            $this->someObjectType,
            $this->someUnionType,
            $this->someInterfaceType,
        ]);
    }

    // Type System: A Schema must have Object root types

    // accepts a Schema whose query type is an object type

    protected function withModifiers($types)
    {
        return \array_merge(
            $types,
            array_map(function ($type) {
                return newList($type);
            }, $types),
            array_map(function ($type) {
                return newNonNull($type);
            }, $types),
            array_map(function ($type) {
                return newNonNull(newList($type));
            }, $types)
        );
    }

    // accepts a Schema whose query and mutation types are object types

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

    // accepts a Schema whose query and subscription types are object types

    protected function expectValid($schema)
    {
        $errors = $this->schemaValidator->validate($schema);
        $this->assertEquals([], $errors);
    }

    // rejects a Schema without a query type

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

    // rejects a Schema whose query root type is not an Object type

    public function testAcceptsASchemaWhoseQueryAndSubscriptionTypesAreObjectTypes(
    )
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

    // rejects a Schema whose mutation type is an input type

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
                'message' => 'Query root type must be provided.',
                'locations' => null,
            ],
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
                'message' => 'Query root type must be provided.',
                'locations' => [locationShorthandToArray([1, 1])],
            ],
        ]);
    }

    // rejects a Schema whose subscription type is an input type

    protected function expectInvalid($schema, $expectedErrors)
    {
        $errors = $this->schemaValidator->validate($schema);
        $this->assertArraySubset($expectedErrors,
            \array_map(function (ValidationExceptionInterface $error) {
                return $error->toArray();
            }, $errors));
    }

    // rejects a Schema whose directives are incorrectly typed

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
                'message' => 'Query root type must be Object type, it cannot be Query.',
                'locations' => [locationShorthandToArray([1, 1])],
            ],
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
                'message' => 'Query root type must be Object type, it cannot be SomeInputObject.',
                'locations' => [locationShorthandToArray([2, 10])],
            ],
        ]);
    }

    // Type System: Objects must have fields

    // accepts an Object type with fields object

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
                'message' => 'Mutation root type must be Object type if provided, it cannot be Mutation.',
                'locations' => [locationShorthandToArray([5, 1])],
            ],
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
                'message' => 'Mutation root type must be Object type if provided, it cannot be SomeInputObject.',
                'locations' => [locationShorthandToArray([3, 13])],
            ],
        ]);
    }

    // rejects an Object type with missing fields

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
                'message' => 'Subscription root type must be Object type if provided, it cannot be Subscription.',
                'locations' => [locationShorthandToArray([5, 1])],
            ],
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
                'message' => 'Subscription root type must be Object type if provided, it cannot be SomeInputObject.',
                'locations' => [locationShorthandToArray([3, 17])],
            ],
        ]);
    }

    // rejects an Object type with incorrectly named fields

    public function testRejectsASchemaWhoseDirectivesAreIncorrectlyTypes()
    {
        $schema = newSchema([
            'query' => $this->someObjectType,
            'directives' => ['somedirective'],
        ]);

        $this->expectInvalid($schema, [
            [
                'message' => 'Expected directive but got: somedirective.',
            ],
        ]);
    }

    // Skip: accepts an Object type with explicitly allowed legacy named fields

    // Skip: throws with bad value for explicitly allowed legacy names

    // Type System: Fields args must be properly named

    // accepts field args with valid names

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

    // rejects field arg with invalid names

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
                'message' => 'Type IncompleteObject must define one or more fields.',
                'locations' => [],
            ],
        ]);

        $manualSchema = $this->schemaWithFieldType(
            newObjectType([
                'name' => 'IncompleteObject',
                'fields' => [],
            ])
        );

        $this->expectInvalid($manualSchema, [
            [
                'message' => 'Type IncompleteObject must define one or more fields.',
            ],
        ]);

        $manualSchema2 = $this->schemaWithFieldType(
            newObjectType([
                'name' => 'IncompleteObject',
                'fields' => function () {
                    return [];
                },
            ])
        );

        $this->expectInvalid($manualSchema2, [
            [
                'message' => 'Type IncompleteObject must define one or more fields.',
            ],
        ]);
    }

    // Type System: Union types must be valid

    // accepts a Union type with member types

    protected function schemaWithFieldType($fieldType)
    {
        return newSchema([
            'query' => newObjectType([
                'name' => 'Query',
                'fields' => ['f' => ['type' => $fieldType]],
            ]),
            'types' => [$fieldType],
        ]);
    }

    // rejects a Union type with empty types

    public function testRejectsAnObjectTypeWithIncorrectlyNamedFields()
    {
        $schema = $this->schemaWithFieldType(
            newObjectType([
                'name' => 'SomeObject',
                'fields' => [
                    'bad-name-with-dashes' => ['type' => String()],
                ],
            ])
        );

        $this->expectInvalid($schema, [
            [
                'message' => 'Names must match /^[_a-zA-Z][_a-zA-Z0-9]*$/ but "bad-name-with-dashes" does not.',
            ],
        ]);
    }

    // rejects a Union type with duplicated member type

    public function testAcceptsFieldArgumentsWithValidNames()
    {
        $schema = $this->schemaWithFieldType(
            newObjectType([
                'name' => 'SomeObject',
                'fields' => [
                    'goodField' => [
                        'type' => String(),
                        'args' => [
                            'goodArg' => ['type' => String()],
                        ],
                    ],
                ],
            ])
        );

        $this->expectValid($schema);
    }

    // rejects a Union type with non-Object members types

    public function testRejectsFieldArgumentsWithInvalidNames()
    {
        $schema = $this->schemaWithFieldType(
            newObjectType([
                'name' => 'SomeObject',
                'fields' => [
                    'badField' => [
                        'type' => String(),
                        'args' => [
                            'bad-name-with-dashes' => ['type' => String()],
                        ],
                    ],
                ],
            ])
        );

        $this->expectInvalid($schema, [
            [
                'message' => 'Names must match /^[_a-zA-Z][_a-zA-Z0-9]*$/ but "bad-name-with-dashes" does not.',
            ],
        ]);
    }

    // Type System: Input Objects must have fields

    // accepts an Input Object type with fields

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

    // rejects an Input Object type with missing fields

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
                'message' => 'Union type BadUnion must define one or more member types.',
                'locations' => [locationShorthandToArray([5, 1])],
            ],
        ]);
    }

    // rejects an Input Object type with incorrectly typed fields

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
                'message' => 'Union type BadUnion can only include type TypeA once.',
                'locations' => locationsShorthandToArray([[14, 5], [16, 5]]),
            ],
        ]);
    }

    // Type System: Enum types must be well defined

    // rejects an Enum type without values

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
                'message' => 'Union type BadUnion can only include Object types, it cannot include String.',
                'locations' => [locationShorthandToArray([15, 5])],
            ],
        ]);

        $badUnionMemberTypes = [
            String(),
            newNonNull($this->someObjectType),
            newList($this->someObjectType),
            $this->someInterfaceType,
            $this->someUnionType,
            $this->someEnumType,
            $this->someInputObjectType,
        ];

        foreach ($badUnionMemberTypes as $memberType) {
            $badSchema = $this->schemaWithFieldType(
                newUnionType(['name' => 'BadUnion', 'types' => [$memberType]])
            );

            $this->expectInvalid($badSchema, [
                [
                    'message' => sprintf(
                        'Union type BadUnion can only include Object types, it cannot include %s.',
                        (string)$memberType
                    ),
                ],
            ]);
        }
    }

    // rejects an Enum type with duplicate values

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

    // rejects an Enum type with incorrectly named values

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
                'message' => 'Input Object type SomeInputObject must define one or more fields.',
                'locations' => [locationShorthandToArray([5, 1])],
            ],
        ]);
    }

    // Type System: Object fields must have output types

    // accepts an output type as an Object field type: ${type}

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
                'message' => 'The type of SomeInputObject.badObject must be Input Type but got: SomeObject.',
                'locations' => [locationShorthandToArray([12, 3])],
            ],
            [
                'message' => 'The type of SomeInputObject.badUnion must be Input Type but got: SomeUnion.',
                'locations' => [locationShorthandToArray([13, 3])],
            ],
        ]);
    }

    // rejects an empty Object field type

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
                'message' => 'Enum type SomeEnum must define one or more values.',
                'locations' => [locationShorthandToArray([5, 1])],
            ],
        ]);
    }

    // rejects a non-output type as an Object field type: ${type}

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
                'message' => 'Enum type SomeEnum can include value SOME_VALUE only once.',
                'locations' => locationsShorthandToArray([[6, 3], [7, 3]]),
            ],
        ]);
    }

    // rejects with relevant locations for a non-output type as an Object field type

    public function testRejectsAnEnumTypeWithIncorrectlyNamedValues()
    {
        $schemaWithEnum = function ($name) {
            return $this->schemaWithFieldType(
                newEnumType([
                    'name' => 'SomeEnum',
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
                    'message' => sprintf('Names must match /^[_a-zA-Z][_a-zA-Z0-9]*$/ but "%s" does not.',
                        $enumValue),
                ],
            ]);
        }

        $forbiddenEnumValues = ['true', 'false', 'null'];

        foreach ($forbiddenEnumValues as $enumValue) {
            $this->expectInvalid($schemaWithEnum($enumValue), [
                [
                    'message' => sprintf('Enum type SomeEnum cannot include value: %s.',
                        $enumValue),
                ],
            ]);
        }
    }

    // Type System: Objects can only implement unique interfaces

    // rejects an Object implementing a non-type values

    public function testAcceptsOutputTypesAsObjectFieldTypes()
    {
        foreach ($this->outputTypes as $outputType) {
            $schema = $this->schemaWithFieldType(
                $this->objectWithFieldOfType($outputType)
            );
            $this->expectValid($schema);
        }
    }

    // rejects an Object implementing a non-Interface type

    protected function objectWithFieldOfType($fieldType)
    {
        return newObjectType([
            'name' => 'BadObject',
            'fields' => [
                'badField' => ['type' => $fieldType],
            ],
        ]);
    }

    // rejects an Object implementing the same interface twice

    public function testRejectsAnEmptyObjectFieldType()
    {
        $schema = $this->schemaWithFieldType(
            $this->objectWithFieldOfType(null)
        );
        $this->expectInvalid($schema, [
            [
                'message' => 'The type of BadObject.badField must be Output Type but got: (null).',
            ],
        ]);
    }

    // rejects an Object implementing the same interface twice due to extension

    public function testRejectsNonOutputTypeAsObjectFieldTypes()
    {
        foreach ($this->noOutputTypes as $notOutputType) {
            $schema = $this->schemaWithFieldType(
                $this->objectWithFieldOfType($notOutputType)
            );
            $this->expectInvalid($schema, [
                [
                    'message' => \sprintf(
                        'The type of BadObject.badField must be Output Type but got: %s.',
                        toString($notOutputType)
                    ),
                ],
            ]);
        }
    }

    // Type System: Interface extensions should be valid

    // rejects an Object implementing the extended interface due to missing field

    public function testRejectsWithLocationsForANonOutputTypeAsAnObjectFieldType(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          field: [SomeInputObject]
        }
        
        input SomeInputObject {
          field: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' => 'The type of Query.field must be Output Type but got: [SomeInputObject].',
                'locations' => [locationShorthandToArray([2, 10])],
            ],
        ]);
    }

    // rejects an Object implementing the extended interface due to missing field args

    public function testRejectsAnObjectImplementingANonTypeValues()
    {
        $schema = newSchema([
            'query' => newObjectType([
                'name' => 'BadObject',
                'fields' => ['f' => ['type' => String()]],
                'interfaces' => [null],
            ]),
        ]);

        $this->expectInvalid($schema, [
            [
                'message' => 'Type BadObject must only implement Interface types, it cannot implement (null).',
            ],
        ]);
    }

    // rejects Objects implementing the extended interface due to mismatching interface type

    public function testRejectsAnObjectImplementingANonInterfaceType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: BadObject
        }
        
        input SomeInputObject {
          field: String
        }
        
        type BadObject implements SomeInputObject {
          field: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' => 'Type BadObject must only implement Interface types, it cannot implement SomeInputObject.',
                'locations' => [locationShorthandToArray([9, 27])],
            ],
        ]);
    }

    // Type System: Interface fields must have output types

    // accepts an output type as an Interface field type: ${type}

    public function testRejectsAnObjectImplementingTheSameInterfaceTwice()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: String
        }
        
        type AnotherObject implements AnotherInterface & AnotherInterface {
          field: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' => 'Type AnotherObject can only implement AnotherInterface once.',
                'locations' => locationsShorthandToArray([[9, 31], [9, 50]]),
            ],
        ]);
    }

    // rejects an empty Interface field type

    public function testRejectsAnObjectImplementingTheSameInterfaceTwiceDueToExtension(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: String
        }
        
        type AnotherObject implements AnotherInterface {
          field: String
        }
        '));

        /** @noinspection PhpUnhandledExceptionInspection */
        $extendedSchema = extendSchema(
            $schema,
            'extend type AnotherObject implements AnotherInterface'
        );

        $this->expectInvalid($extendedSchema, [
            [
                'message' => 'Type AnotherObject can only implement AnotherInterface once.',
                'locations' => locationsShorthandToArray([[9, 31], [1, 38]]),
            ],
        ]);
    }

    // rejects a non-output type as an Interface field type: ${type}

    public function testRejectsAnObjectImplementingTheExtendedInterfaceDueToMissingField(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: String
        }
        
        type AnotherObject implements AnotherInterface {
          field: String
        }
        '));

        /** @noinspection PhpUnhandledExceptionInspection */
        $extendedSchema = extendSchema(
            $schema,
            dedent('
            extend interface AnotherInterface {
              newField: String
            }
            ')
        );

        $this->expectInvalid($extendedSchema, [
            [
                'message' =>
                    'Interface field AnotherInterface.newField expected '.
                    'but AnotherObject does not provide it.',
                'locations' => locationsShorthandToArray([[2, 3], [9, 1]]),
            ],
        ]);
    }

    // rejects a non-output type as an Interface field type with locations

    public function testRejectsAnObjectImplementingTheExtendedInterfaceDueToMissingFieldArguments(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: String
        }
        
        type AnotherObject implements AnotherInterface {
          field: String
        }
        '));

        /** @noinspection PhpUnhandledExceptionInspection */
        $extendedSchema = extendSchema(
            $schema,
            dedent('
            extend interface AnotherInterface {
              newField(test: Boolean): String
            }
            
            extend type AnotherObject {
              newField: String
            }
            ')
        );

        $this->expectInvalid($extendedSchema, [
            [
                'message' =>
                    'Interface field argument AnotherInterface.newField(test:) expected '.
                    'but AnotherObject.newField does not provide it.',
                'locations' => locationsShorthandToArray([[2, 12], [6, 3]]),
            ],
        ]);
    }

    // Type System: Field arguments must have input types

    // accepts an input type as a field arg type: ${type}

    public function testRejectsObjectsImplementingTheExtendedInterfaceDueToMisMatchingInterfaceType(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: String
        }
        
        type AnotherObject implements AnotherInterface {
          field: String
        }
        '));

        /** @noinspection PhpUnhandledExceptionInspection */
        $extendedSchema = extendSchema(
            $schema,
            dedent('
            extend interface AnotherInterface {
              newInterfaceField: NewInterface
            }
            
            interface NewInterface {
              newField: String
            }
            
            interface MismatchingInterface {
              newField: String
            }
            
            extend type AnotherObject {
              newInterfaceField: MismatchingInterface
            }
            ')
        );

        $this->expectInvalid($extendedSchema, [
            [
                'message' =>
                    'Interface field AnotherInterface.newInterfaceField expects type NewInterface but '.
                    'AnotherObject.newInterfaceField is type MismatchingInterface.',
                'locations' => locationsShorthandToArray([[2, 22], [14, 22]]),
            ],
        ]);
    }

    // rejects an empty field arg type

    public function testAcceptsOutputTypesAsInterfaceFieldTypes()
    {
        foreach ($this->outputTypes as $outputType) {
            $schema = $this->schemaWithFieldType(
                $this->interfaceWithFieldOfType($outputType)
            );
            $this->expectValid($schema);
        }
    }

    // rejects a non-input type as a field arg type: ${type}

    protected function interfaceWithFieldOfType($fieldType)
    {
        return newInterfaceType([
            'name' => 'BadInterface',
            'fields' => [
                'badField' => ['type' => $fieldType],
            ],
        ]);
    }

    // rejects a non-input type as a field arg with locations

    public function testRejectsAnEmptyInterfaceFieldType()
    {
        $schema = $this->schemaWithFieldType(
            $this->interfaceWithFieldOfType(null)
        );
        $this->expectInvalid($schema, [
            [
                'message' => 'The type of BadInterface.badField must be Output Type but got: (null).',
            ],
        ]);
    }

    // Type System: Input Object fields must have input types

    // accepts an input type as an input field type: ${type}

    public function testRejectsNonOutputTypesAsInterfaceFieldTypes()
    {
        foreach ($this->noOutputTypes as $notOutputType) {
            $schema = $this->schemaWithFieldType(
                $this->interfaceWithFieldOfType($notOutputType)
            );
            $this->expectInvalid($schema, [
                [
                    'message' => \sprintf(
                        'The type of BadInterface.badField must be Output Type but got: %s.',
                        toString($notOutputType)
                    ),
                ],
            ]);
        }
    }

    // rejects an empty input field type

    public function testRejectsWithLocationsForANonOutputTypeAsAnInterfaceFieldType(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: SomeInterface
        }
        
        interface SomeInterface {
          field: SomeInputObject
        }
        
        input SomeInputObject {
          foo: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' => 'The type of SomeInterface.field must be Output Type but got: SomeInputObject.',
                'locations' => [locationShorthandToArray([6, 10])],
            ],
        ]);
    }

    // rejects a non-input type as an input field type: ${type}

    public function testAcceptsInputTypesAsFieldArgumentTypes()
    {
        foreach ($this->inputTypes as $inputType) {
            $schema = $this->schemaWithFieldType(
                $this->objectWithFieldArgumentOfType($inputType)
            );
            $this->expectValid($schema);
        }
    }

    // rejects a non-input type as an input object field with locations

    protected function objectWithFieldArgumentOfType($argumentType)
    {
        return newObjectType([
            'name' => 'BadObject',
            'fields' => [
                'badField' => [
                    'type' => String(),
                    'args' => [
                        'badArg' => ['type' => $argumentType],
                    ],
                ],
            ],
        ]);
    }

    // Objects must adhere to Interface they implement

    // accepts an Object which implements an Interface

    public function testRejectsAnEmptyFieldArgumentType()
    {
        $schema = $this->schemaWithFieldType(
            $this->objectWithFieldArgumentOfType(null)
        );
        $this->expectInvalid($schema, [
            [
                'message' => 'The type of BadObject.badField(badArg:) must be Input Type but got: (null).',
            ],
        ]);
    }

    // accepts an Object which implements an Interface along with more fields

    public function testRejectsNonInputTypesAsFieldArgumentTypes()
    {
        foreach ($this->noInputTypes as $notInputType) {
            $schema = $this->schemaWithFieldType(
                $this->objectWithFieldArgumentOfType($notInputType)
            );
            $this->expectInvalid($schema, [
                [
                    'message' => \sprintf(
                        'The type of BadObject.badField(badArg:) must be Input Type but got: %s.',
                        toString($notInputType)
                    ),
                ],
            ]);
        }
    }

    // accepts an Object which implements an Interface field along with additional optional arguments

    public function testRejectsWithLocationsForANonInputTypeAsAFieldArgumentType(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test(arg: SomeObject): String
        }
        
        type SomeObject {
          foo: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' => 'The type of Query.test(arg:) must be Input Type but got: SomeObject.',
                'locations' => [locationShorthandToArray([2, 8])],
            ],
        ]);
    }

    // rejects an Object missing an Interface field

    public function testAcceptsInputTypesAsInputFieldTypes()
    {
        foreach ($this->inputTypes as $inputType) {
            $schema = $this->schemaWithFieldType(
                $this->inputObjectWithFieldOfType($inputType)
            );
            $this->expectValid($schema);
        }
    }

    // rejects an Object with an incorrectly typed Interface field

    protected function inputObjectWithFieldOfType($fieldType)
    {
        return newObjectType([
            'name' => 'BadObject',
            'fields' => [
                'badField' => [
                    'type' => String(),
                    'args' => [
                        'badArg' => [
                            'type' => newInputObjectType([
                                'name' => 'BadInputObject',
                                'fields' => [
                                    'badField' => ['type' => $fieldType],
                                ],
                            ]),
                        ],
                    ],
                ],
            ],
        ]);
    }

    // rejects an Object with a differently typed Interface field

    public function testRejectsAnEmptyInputFieldType()
    {
        $schema = $this->schemaWithFieldType(
            $this->inputObjectWithFieldOfType(null)
        );
        $this->expectInvalid($schema, [
            [
                'message' => 'The type of BadInputObject.badField must be Input Type but got: (null).',
            ],
        ]);
    }

    // accepts an Object with a subtyped Interface field (interface)

    public function testRejectsNonInputTypesAsInputFieldTypes()
    {
        foreach ($this->noInputTypes as $notInputType) {
            $schema = $this->schemaWithFieldType(
                $this->inputObjectWithFieldOfType($notInputType)
            );
            $this->expectInvalid($schema, [
                [
                    'message' => \sprintf(
                        'The type of BadInputObject.badField must be Input Type but got: %s.',
                        toString($notInputType)
                    ),
                ],
            ]);
        }
    }

    // accepts an Object with a subtyped Interface field (union)

    public function testRejectsWithLocationsForANonInputTypeAsAInputFieldType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
        test(arg: SomeInputObject): String
        }
        
        input SomeInputObject {
          foo: SomeObject
        }
        
        type SomeObject {
          bar: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' => 'The type of SomeInputObject.foo must be Input Type but got: SomeObject.',
                'locations' => [locationShorthandToArray([6, 3])],
            ],
        ]);
    }

    // rejects an Object missing an Interface argument

    public function testAcceptsAnObjectWhichImplementsAnInterface()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field(input: String): String
        }
        
        type AnotherObject implements AnotherInterface {
          field(input: String): String
        }
        '));

        $this->expectValid($schema);
    }

    // rejects an Object with an incorrectly typed Interface argument

    public function testAcceptAnObjectWhichImplementsAnInterfaceAlongWithMoreFields(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field(input: String): String
        }
        
        type AnotherObject implements AnotherInterface {
          field(input: String): String
          anotherField: String
        }
        '));

        $this->expectValid($schema);
    }

    // rejects an Object with both an incorrectly typed field and argument

    public function testAcceptsAnObjectWhichImplementsAnInterfaceFieldAlongWithAdditionalOptionalArguments(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field(input: String): String
        }
        
        type AnotherObject implements AnotherInterface {
          field(input: String, anotherInput: String): String
        }
        '));

        $this->expectValid($schema);
    }

    // rejects an Object which implements an Interface field along with additional required arguments

    public function testRejectsAnObjectMissingAnInterfaceField()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field(input: String): String
        }
        
        type AnotherObject implements AnotherInterface {
          anotherField: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' => 'Interface field AnotherInterface.field expected but AnotherObject does not provide it.',
                'locations' => locationsShorthandToArray([[6, 3], [9, 1]]),
            ],
        ]);
    }

    // accepts an Object with an equivalently wrapped Interface field type

    public function testRejectsAnObjectWithIncorrectlyTypedInterfaceField()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field(input: String): String
        }
        
        type AnotherObject implements AnotherInterface {
          field(input: String): Int
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' =>
                    'Interface field AnotherInterface.field expects '.
                    'type String but AnotherObject.field is type Int.',
                'locations' => locationsShorthandToArray([[6, 25], [10, 25]]),
            ],
        ]);
    }

    // rejects an Object with a non-list Interface field list type

    public function testRejectsAnObjectWithADifferentlyTypedInterfaceField()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        type A { foo: String }
        type B { foo: String }
        
        interface AnotherInterface {
          field: A
        }
        
        type AnotherObject implements AnotherInterface {
          field: B
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' => 'Interface field AnotherInterface.field expects type A but AnotherObject.field is type B.',
                'locations' => locationsShorthandToArray([[9, 10], [13, 10]]),
            ],
        ]);
    }

    // rejects an Object with a list Interface field non-list type

    public function testAcceptsAnObjectWithASubtypedInterfaceField()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: AnotherInterface
        }
        
        type AnotherObject implements AnotherInterface {
          field: AnotherObject
        }
        '));

        $this->expectValid($schema);
    }

    // accepts an Object with a subset non-null Interface field type

    public function testAcceptAnObjectWithASubtypedInterfaceField()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        type SomeObject {
          field: String
        }
        
        union SomeUnionType = SomeObject
        
        interface AnotherInterface {
          field: SomeUnionType
        }
        
        type AnotherObject implements AnotherInterface {
          field: SomeObject
        }
        '));

        $this->expectValid($schema);
    }

    // rejects an Object with a superset nullable Interface field type

    public function testRejectsAnObjectMissingAnInterfaceArgument()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field(input: String): String
        }
        
        type AnotherObject implements AnotherInterface {
          field: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' =>
                    'Interface field argument AnotherInterface.field(input:) expected '.
                    'but AnotherObject.field does not provide it.',
                'locations' => locationsShorthandToArray([[6, 9], [10, 3]]),
            ],
        ]);
    }

    public function testRejectsAnObjectWithAnIncorrectlyTypedInterfaceArgument()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field(input: String): String
        }
        
        type AnotherObject implements AnotherInterface {
          field(input: Int): String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' =>
                    'Interface field argument AnotherInterface.field(input:) expects '.
                    'type String but AnotherObject.field(input:) is type Int.',
                'locations' => locationsShorthandToArray([[6, 16], [10, 16]]),
            ],
        ]);
    }

    public function testRejectsAnObjectWithBothAnIncorrectlyTypedFieldAndArgument(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field(input: String): String
        }
        
        type AnotherObject implements AnotherInterface {
          field(input: Int): Int
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' =>
                    'Interface field AnotherInterface.field expects type String but '.
                    'AnotherObject.field is type Int.',
                'locations' => locationsShorthandToArray([[6, 25], [10, 22]]),
            ],
            [
                'message' =>
                    'Interface field argument AnotherInterface.field(input:) expects '.
                    'type String but AnotherObject.field(input:) is type Int.',
                'locations' => locationsShorthandToArray([[6, 16], [10, 16]]),
            ],
        ]);
    }

    public function testRejectsAnObjectWhichImplementsAnInterfaceFieldAlongWithAdditionalRequiredArguments(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field(input: String): String
        }
        
        type AnotherObject implements AnotherInterface {
          field(input: String, anotherInput: String!): String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' =>
                    'Object field argument AnotherObject.field(anotherInput:) is of '.
                    'required type String! but is not also provided by the Interface '.
                    'field AnotherInterface.field.',
                'locations' => locationsShorthandToArray([[10, 24], [6, 3]]),
            ],
        ]);
    }

    public function testAcceptsAnObjectWithAnEquivalentlyWrappedInterfaceFieldType(
    )
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: [String]!
        }
        
        type AnotherObject implements AnotherInterface {
          field: [String]!
        }
        '));

        $this->expectValid($schema);
    }

    public function testRejectsAnObjectWithANonListInterfaceFieldListType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: [String]
        }
        
        type AnotherObject implements AnotherInterface {
          field: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' =>
                    'Interface field AnotherInterface.field expects type [String] '.
                    'but AnotherObject.field is type String.',
                'locations' => locationsShorthandToArray([[6, 10], [10, 10]]),
            ],
        ]);
    }

    public function testRejectsAnObjectWithAListInterfaceFieldNonListType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: String
        }
        
        type AnotherObject implements AnotherInterface {
          field: [String]
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' =>
                    'Interface field AnotherInterface.field expects type String but '.
                    'AnotherObject.field is type [String].',
                'locations' => locationsShorthandToArray([[6, 10], [10, 10]]),
            ],
        ]);
    }

    public function testAcceptsAnObjectWithASubsetNonNullInterfaceFieldType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: String
        }
        
        type AnotherObject implements AnotherInterface {
          field: String!
        }
        '));

        $this->expectValid($schema);
    }

    public function testRejectsAnObjectWithASupersetNullableInterfaceFieldType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = buildSchema(dedent('
        type Query {
          test: AnotherObject
        }
        
        interface AnotherInterface {
          field: String!
        }
        
        type AnotherObject implements AnotherInterface {
          field: String
        }
        '));

        $this->expectInvalid($schema, [
            [
                'message' =>
                    'Interface field AnotherInterface.field expects type String! '.
                    'but AnotherObject.field is type String.',
                'locations' => locationsShorthandToArray([[6, 10], [10, 10]]),
            ],
        ]);
    }
}
