<?php

namespace Digia\GraphQL\Test\Functional\Execution;


use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Language\Node\ValueAwareInterface;
use Digia\GraphQL\Schema\Schema;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Test\jsonEncode;
use function Digia\GraphQL\Type\newInputObjectType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newScalarType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;


/**
 * Class VariablesTest
 * @package Digia\GraphQL\Test\Functional\Execution
 */
class VariablesTest extends TestCase
{
    private $schema;


    /**
     * @noinspection PhpDocMissingThrowsInspection
     */
    protected function setUp()
    {
        parent::setUp();

        /** @noinspection PhpUnhandledExceptionInspection */
        $TestComplexScalar = newScalarType([
            'name'         => 'ComplexScalar',
            'serialize'    => function ($value) {
                if ($value === 'DeserializedValue') {
                    return 'SerializedValue';
                }
                return null;
            },
            'parseValue'   => function ($value) {
                if ($value === 'SerializedValue') {
                    return 'DeserializedValue';
                }
                return null;
            },
            'parseLiteral' => function (ValueAwareInterface $ast) {
                if ($ast->getValue() === 'SerializedValue') {
                    return 'DeserializedValue';
                }
                return null;
            }
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $TestInputObject = newInputObjectType([
            'name'   => 'TestInputObject',
            'fields' => [
                'a' => ['type' => stringType()],
                'b' => ['type' => newList(stringType())],
                'c' => ['type' => newNonNull(stringType())],
                'd' => ['type' => $TestComplexScalar]
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $TestNestedInputObject = newInputObjectType([
            'name'   => 'TestNestedInputObject',
            'fields' => [
                'na' => ['type' => newNonNull($TestInputObject)],
                'nb' => ['type' => newNonNull(stringType())]
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $TestType = newObjectType([
            'name'   => 'TestType',
            'fields' => [
                'fieldWithObjectInput'                                   => $this->fieldWithInputArg(['type' => $TestInputObject]),
                'fieldWithNullableStringInput'                           => $this->fieldWithInputArg(['type' => stringType()]),
                'fieldWithNonNullableStringInput'                        => $this->fieldWithInputArg([
                    'type' => newNonNull(stringType())
                ]),
                'fieldWithDefaultArgumentValue'                          => $this->fieldWithInputArg([
                    'type'         => stringType(),
                    'defaultValue' => 'Hello World'
                ]),
                'fieldWithNonNullableStringInputAndDefaultArgumentValue' => $this->fieldWithInputArg([
                    'type'         => newNonNull(stringType()),
                    'defaultValue' => 'Hello World'
                ]),
                'fieldWithNestedInputObject'                             => $this->fieldWithInputArg([
                    'type'         => $TestNestedInputObject,
                    'defaultValue' => 'Hello World'
                ]),
                'list'                                                   => $this->fieldWithInputArg([
                    'type' => newList(stringType())
                ]),
                'nnList'                                                 => $this->fieldWithInputArg([
                    'type' => newNonNull(newList(stringType())),
                ]),
                'listNN'                                                 => $this->fieldWithInputArg([
                    'type' => newList(newNonNull(stringType())),
                ]),
                'nnListNN'                                               => $this->fieldWithInputArg([
                    'type' => newNonNull(newList(newNonNull(stringType()))),
                ]),
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->schema = newSchema([
            'query' => $TestType
        ]);
    }

    /**
     * @param array $inputArg
     * @return array
     */
    function fieldWithInputArg(array $inputArg): array
    {
        return [
            'type'    => stringType(),
            'args'    => [
                'input' => $inputArg
            ],
            'resolve' => function ($_, $args) {
                return isset($args['input']) ? jsonEncode($args['input']) : null;
            }
        ];
    }

    // Execute: Handles inputs

    // Handles objects and nullability

    public function testExecutesWithComplexInput()
    {
        $this->assertQueryResult(
            '{
              fieldWithObjectInput(input: {a: "foo", b: ["bar"], c: "baz"})
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}'
                ]
            ]
        );
    }

    public function testVariableNotProvided()
    {
        $this->assertQueryResult(
            'query q($input: String) {
              fieldWithNullableStringInput(input: $input)
            }',
            [
                'data' => [
                    'fieldWithNullableStringInput' => null
                ]
            ],
            // Intentionally missing variable values.
            []
        );
    }

    public function testVariableWithExplicitNullValue()
    {
        $this->markTestIncomplete('Requires proper support for null values.');

        $this->assertQueryResult(
            'query q($input: String) {
              fieldWithNullableStringInput(input: $input)
            }',
            [
                'data' => [
                    'fieldWithNullableStringInput' => 'null',
                ]
            ],
            ['input' => null]
        );
    }

    public function testUsesDefaultValueWhenValueNotProvided()
    {
        $this->assertQueryResult(
            'query ($input: TestInputObject = {a: "foo", b: ["bar"], c: "baz"}) {
              fieldWithObjectInput(input: $input)
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}',
                ]
            ]
        );
    }

    public function testDoesNotUseDefaultValueWhenValueProvided()
    {
        $this->assertQueryResult(
            'query ($input: String = "Default value") {
              fieldWithNullableStringInput(input: $input)
            }',
            [
                'data' => [
                    'fieldWithNullableStringInput' => '"Variable value"',
                ]
            ],
            ['input' => 'Variable value']
        );
    }

    public function testUsesExplicitNullValueInsteadOfDefaultValue()
    {
        $this->markTestIncomplete('Requires proper support for null values.');

        $this->assertQueryResult(
            'query ($input: String = "Default value") {
              fieldWithNullableStringInput(input: $input)
            }',
            [
                'data' => [
                    'fieldWithNullableStringInput' => 'null',
                ]
            ],
            ['input' => null]
        );
    }

    public function testUsesNullDefaultValueWhenValueNotProvided()
    {
        $this->markTestIncomplete('Requires proper support for null values.');

        $this->assertQueryResult(
            'query ($input: String = null) {
              fieldWithNullableStringInput(input: $input)
            }',
            [
                'data' => [
                    'fieldWithNullableStringInput' => 'null',
                ]
            ],
            // Intentionally missing variable values.
            []
        );
    }

    public function testProperlyParsesSingleValueToList()
    {
        $this->assertQueryResult(
            '{
              fieldWithObjectInput(input: {a: "foo", b: "bar", c: "baz"})
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}'
                ]
            ]
        );
    }

    public function testProperlyParsesNullValueToNull()
    {
        $this->assertQueryResult(
            '{
              fieldWithObjectInput(input: {a: null, b: null, c: "C", d: null})
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"a":null,"b":null,"c":"C","d":null}'
                ]
            ]
        );
    }

    public function testProperlyParsesNullValueInList()
    {
        $this->assertQueryResult(
            '{
              fieldWithObjectInput(input: {b: ["A",null,"C"], c: "C"})
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"b":["A",null,"C"],"c":"C"}'
                ]
            ]
        );
    }

    public function testDoesNotUseIncorrectValue()
    {
        $this->assertQueryResult(
            '{
              fieldWithObjectInput(input: ["foo", "bar", "baz"])
            }',
            [
                'data'   => [
                    'fieldWithObjectInput' => null
                ],
                'errors' => [
                    [
                        'message'   => 'Argument "input" has invalid value ["foo","bar","baz"].',
                        'path'      => ['fieldWithObjectInput'],
                        'locations' => [['line' => 2, 'column' => 43]],
                    ]
                ]
            ]
        );
    }

    public function testProperlyRunsParseLiteralOnComplexScalarTypes()
    {
        $this->assertQueryResult(
            '{
                fieldWithObjectInput(input: {c: "foo", d: "SerializedValue"})
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"c":"foo","d":"DeserializedValue"}'
                ]
            ]
        );
    }

    // USING VARIABLES

    public function testExecutesWithComplexInputUsingVariables()
    {
        $this->assertQueryResult(
            'query ($input: TestInputObject) {
              fieldWithObjectInput(input: $input)
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}'
                ]
            ],
            ['input' => ['a' => 'foo', 'b' => ['bar'], 'c' => 'baz']]
        );
    }

    public function testUseDefaultValueWhenNotProvide()
    {
        $this->assertQueryResult(
            'query ($input: TestInputObject = {a: "foo", b: ["bar"], c: "baz"}) {
              fieldWithObjectInput(input: $input)
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}'
                ]
            ]
        );
    }

    public function testProperlyParsesSingleValueToListUsingVariable()
    {
        $this->assertQueryResult(
            'query ($input: TestInputObject) {
              fieldWithObjectInput(input: $input)
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}'
                ]
            ],
            ['input' => ['a' => 'foo', 'b' => 'bar', 'c' => 'baz']]
        );
    }

    public function testExecutesWithComplexScalarInputUsingVariable()
    {
        $this->assertQueryResult(
            'query ($input: TestInputObject) {
              fieldWithObjectInput(input: $input)
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"c":"foo","d":"DeserializedValue"}'
                ]
            ],
            ['input' => ['c' => 'foo', 'd' => 'SerializedValue']]
        );
    }

    public function testErrorsOnNullForNestedNonNullUsingVariable()
    {
        $this->assertQueryResult(
            'query ($input: TestInputObject) {
              fieldWithObjectInput(input: $input)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$input" got invalid value {"a":"foo","b":"bar","c":null}; ' .
                            'Field value.c of required type String! was not provided.',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => []
                    ]
                ]
            ],
            ['input' => ['a' => 'foo', 'b' => 'bar', 'c' => null]]
        );
    }

    public function testErrorsOnDeepNestedErrorsWithManyErrors()
    {
        $this->assertQueryResult(
            'query ($input: TestNestedInputObject) {
              fieldWithNestedObjectInput(input: $input)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$input" got invalid value {"na":{"a":"foo"}}; ' .
                            'Field value.na.c of required type String! was not provided.',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => []
                    ],
                    [
                        'message'   => 'Variable "$input" got invalid value {"na":{"a":"foo"}}; ' .
                            'Field value.nb of required type String! was not provided.',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => []
                    ]
                ]
            ],
            ['input' => ['na' => ['a' => 'foo']]]
        );
    }

    public function testErrorsOnAdditionOfUnknownInputField()
    {
        $this->assertQueryResult(
            'query ($input: TestInputObject) {
              fieldWithObjectInput(input: $input)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$input" got invalid value ' .
                            '{"a":"foo","b":"bar","c":"baz","extra":"dog"}; ' .
                            'Field "extra" is not defined by type TestInputObject.',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => []
                    ]
                ]
            ],
            ['input' => ['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'extra' => 'dog']]
        );
    }

    // HANDLES NULLABLE SCALARS

    public function testAllowsNullableInputsToBeOmitted()
    {
        $this->assertQueryResult(
            '{
              fieldWithNullableStringInput
            }',
            [
                'data' => [
                    'fieldWithNullableStringInput' => null
                ],
            ]
        );
    }

    public function testAllowsNullableInputsToBeOmittedInAVariable()
    {
        $this->assertQueryResult(
            'query ($value: String) {
              fieldWithNullableStringInput(input: $value)
            }',
            [
                'data' => [
                    'fieldWithNullableStringInput' => null
                ],
            ]
        );
    }


    public function testAllowsNullableInputsToBeOmittedInUnlistedVariable()
    {
        $this->assertQueryResult(
            'query {
              fieldWithNullableStringInput(input: $value)
            }',
            [
                'data' => [
                    'fieldWithNullableStringInput' => null
                ],
            ]
        );
    }

    public function testAllowsNullableInputsToBeSetToNullInVariable()
    {
        $this->assertQueryResult(
            'query ($value: String) {
              fieldWithNullableStringInput(input: $value)
            }',
            [
                'data' => [
                    'fieldWithNullableStringInput' => null
                ],
            ],
            ['value' => null]
        );
    }

    public function testAllowsNullableInputsToBeSetToAValueDirectly()
    {
        $this->assertQueryResult(
            'query ($value: String) {
              fieldWithNullableStringInput(input: "a")
            }',
            [
                'data' => [
                    'fieldWithNullableStringInput' => '"a"'
                ],
            ]
        );
    }

    // HANDLES NON-NULLABLE SCALARS'

    public function testAllowsNonNullableInputsToBeOmittedGivenADefault()
    {
        $this->assertQueryResult(
            'query ($value: String = "default") {
              fieldWithNonNullableStringInput(input: $value)
            }',
            [
                'data' => [
                    'fieldWithNonNullableStringInput' => '"default"'
                ],
            ]
        );
    }

    public function testDoesNotAllowNonNullableInputsToBeOmittedInAVariable()
    {
        $this->assertQueryResult(
            'query ($value: String!) {
              fieldWithNonNullableStringInput(input: $value)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$value" of required type "String!" was not provided.',
                        'locations' => [
                            [
                                'column' => 8,
                                'line'   => 1
                            ]
                        ],
                        'path'      => []
                    ]
                ],
            ]
        );
    }

    public function testDoesNotAllowNonNullabeInputsToBeSetToNullInAVariable()
    {
        $this->assertQueryResult(
            'query ($value: String!) {
              fieldWithNonNullableStringInput(input: $value)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$value" of required type "String!" was not provided.',
                        'locations' => [
                            [
                                'column' => 8,
                                'line'   => 1
                            ]
                        ],
                        'path'      => []
                    ]
                ],
            ],
            ['value' => null]
        );
    }

    public function testAllowsNonNullableInputsToBeSetToAValueInAVariable()
    {
        $this->assertQueryResult(
            'query ($value: String!) {
              fieldWithNonNullableStringInput(input: $value)
            }',
            [
                'data' => [
                    'fieldWithNonNullableStringInput' => '"a"'
                ]
            ],
            ['value' => 'a']
        );
    }

    public function testAllowsNonNullableInputsToBeSetToAValueDirectly()
    {
        $this->assertQueryResult(
            '{
              fieldWithNonNullableStringInput(input: "a")
            }',
            [
                'data' => [
                    'fieldWithNonNullableStringInput' => '"a"'
                ]
            ]
        );
    }

    public function testReportErrorForMissingNonNullableInputs()
    {
        $this->assertQueryResult(
            '{ fieldWithNonNullableStringInput }',
            [
                'data'   => [
                    'fieldWithNonNullableStringInput' => null
                ],
                'errors' => [
                    [
                        'message'   => 'Argument "input" of required type "String!" was not provided.',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 3
                            ]
                        ],
                        'path'      => ['fieldWithNonNullableStringInput']
                    ]
                ]
            ]
        );
    }

    public function testReportErrorForArrayPassedIntoStringInput()
    {
        $this->assertQueryResult(
            'query ($value: String!) {
              fieldWithNonNullableStringInput(input: $value)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$value" got invalid value [1,2,3]; Expected type String; ' .
                            'String cannot represent a non-scalar value',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => [] // @TODO Check path
                    ]
                ]
            ],
            ['value' => [1, 2, 3]]
        );
    }

    public function testSerializingAnArrayViaGraphQLStringThrowsTypeError()
    {
        $this->expectException(InvalidTypeException::class);

        stringType()->serialize([1, 2, 3]);
    }

    public function testReportErrorForNonProvidedVariableForNonNullableInputs()
    {
        // Note: this test would typically fail validation before encountering
        // this execution error, however for queries which previously validated
        // and are being run against a new schema which have introduced a breaking
        // change to make a formerly non-required argument required, this asserts
        // failure before allowing the underlying code to receive a non-null value.

        $this->assertQueryResult(
            '{
              fieldWithNonNullableStringInput(input: $foo)
            }',
            [
                'data'   => [
                    'fieldWithNonNullableStringInput' => null
                ],
                'errors' => [
                    [
                        'message'   => 'Argument "input" of required type "String!" was provided the ' .
                            'variable "$foo" which was not provided a runtime value.',
                        'locations' => [
                            [
                                'line'   => 2,
                                'column' => 54
                            ]
                        ],
                        'path'      => ['fieldWithNonNullableStringInput']
                    ]
                ]
            ]
        );
    }

    // HANDLES LISTS AND NULLABILITY

    public function testAllowsListsToBeNull()
    {
        $this->assertQueryResult(
            'query ($input: [String]) {
              list(input: $input)
            }',
            [
                'data' => [
                    'list' => null
                ],
            ],
            ['input' => null]
        );
    }

    public function testAllowsListsContainValues()
    {
        $this->assertQueryResult(
            'query ($input: [String]) {
              list(input: $input)
            }',
            [
                'data' => [
                    'list' => '["A"]'
                ],
            ],
            ['input' => ['A']]
        );
    }


    public function testAllowsListsContainNull()
    {
        $this->assertQueryResult(
            'query ($input: [String]) {
              list(input: $input)
            }',
            [
                'data' => [
                    'list' => '["A",null,"B"]'
                ],
            ],
            ['input' => ['A', null, 'B']]
        );
    }

    public function testDoesNotAllowNonNullListsToBeNull()
    {
        $this->assertQueryResult(
            'query ($input: [String]!) {
              nnList(input: $input)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$input" of required type "[String]!" was not provided.',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => []
                    ]
                ],
            ],
            ['input' => null]
        );
    }


    public function testAllowsNonNullListsToContainValues()
    {
        $this->assertQueryResult(
            'query ($input: [String]!) {
              nnList(input: $input)
            }',
            [
                'data' => [
                    'nnList' => '["A"]'
                ]
            ],
            ['input' => ['A']]
        );
    }

    public function testAllowsListsOfNonNullsToBeNull()
    {
        $this->assertQueryResult(
            'query ($input: [String!]) {
              listNN(input: $input)
            }',
            [
                'data' => [
                    'listNN' => null
                ]
            ],
            ['input' => null]
        );
    }

    public function testAllowsListsOfNonNullsToContainValues()
    {
        $this->assertQueryResult(
            'query ($input: [String!]) {
              listNN(input: $input)
            }',
            [
                'data' => [
                    'listNN' => '["A"]'
                ]
            ],
            ['input' => ['A']]
        );
    }

    public function testDoesNotAllowsListsOfNonNullsToContainNull()
    {
        $this->assertQueryResult(
            'query ($input: [String!]) {
              listNN(input: $input)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$input" got invalid value ["A",null,"B"]; ' .
                            'Expected non-nullable type String! not to be null at value[1].',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => []
                    ]
                ],
            ],
            ['input' => ['A', null, 'B']]
        );
    }

    public function testDoesNotAllowsListsOfNonNullsToBeNull()
    {
        $this->assertQueryResult(
            'query ($input: [String!]!) {
              nnListNN(input: $input)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$input" of required type "[String!]!" was not provided.',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => []
                    ]
                ],
            ],
            ['input' => null]
        );
    }

    public function testDoesNotAllowsNonNullListsOfNonNullsToContainNull()
    {
        $this->assertQueryResult(
            'query ($input: [String!]!) {
              nnListNN(input: $input)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$input" got invalid value ["A",null,"B"]; ' .
                            'Expected non-nullable type String! not to be null at value[1].',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => []
                    ]
                ],
            ],
            ['input' => ['A', null, 'B']]
        );
    }

    public function testDoesNotAllowsInvalidTypesToBeUsedAsValues()
    {
        $this->assertQueryResult(
            'query ($input: TestType!) {
              fieldWithObjectInput(input: $input)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$input" expected value of type "TestType!" which ' .
                            'cannot be used as an input type.',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => []
                    ]
                ],
            ],
            [
                'input' => [
                    'list' => [
                        'A',
                        null,
                        'B'
                    ]
                ]
            ]
        );
    }

    public function testDoesNotAllowsUnknownTypesToBeUsedAsValues()
    {
        $this->assertQueryResult(
            'query ($input: UnknownType!) {
              fieldWithObjectInput(input: $input)
            }',
            [
                'data'   => null,
                'errors' => [
                    [
                        'message'   => 'Variable "$input" expected value of type "UnknownType!" which ' .
                            'cannot be used as an input type.',
                        'locations' => [
                            [
                                'line'   => 1,
                                'column' => 8
                            ]
                        ],
                        'path'      => []
                    ]
                ],
            ],
            ['input' => 'whoknows']
        );
    }

    // Execute: Uses argument default values

    public function testWhenNoArgumentProvided()
    {
        $this->assertQueryResult(
            '{ fieldWithDefaultArgumentValue }',
            [
                'data' => ['fieldWithDefaultArgumentValue' => '"Hello World"']
            ]
        );
    }

    public function testWhenOmittedVariableProvied()
    {
        $this->assertQueryResult(
            'query ($optional: String) {
              fieldWithDefaultArgumentValue(input: $optional)
            }',
            [
                'data' => ['fieldWithDefaultArgumentValue' => '"Hello World"']
            ]
        );
    }

    public function testNotWhenArgumentCannotBeCoerced()
    {
        $this->assertQueryResult(
            '{
              fieldWithDefaultArgumentValue(input: WRONG_TYPE)
            }',
            [
                'data'   => [
                    'fieldWithDefaultArgumentValue' => null
                ],
                'errors' => [
                    [
                        'message'   => 'Argument "input" has invalid value WRONG_TYPE.',
                        'locations' => [
                            [
                                'line'   => 2,
                                'column' => 52
                            ]
                        ],
                        'path'      => ['fieldWithDefaultArgumentValue']
                    ]
                ],
            ]
        );
    }

    public function testCustomDateTimeScalarType()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $dateType = newScalarType([
            'name'         => 'Date',
            'serialize'    => function (\DateTime $value) {
                /** @noinspection PhpUndefinedMethodInspection */
                return $value->format('Y-m-d');
            },
            'parseValue'   => function ($node) {
                /** @noinspection PhpUndefinedMethodInspection */
                return new \DateTime($node->getValue(), new \DateTimeZone('Europe/Helsinki'));
            },
            'parseLiteral' => function ($node) {
                /** @noinspection PhpUndefinedMethodInspection */
                return new \DateTime($node->getValue(), new \DateTimeZone('Europe/Helsinki'));
            },
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $TestInputObject = newInputObjectType([
            'name'   => 'TestInputObject',
            'fields' => [
                'a' => ['type' => stringType()],
                'b' => ['type' => newList(stringType())],
                'c' => ['type' => newNonNull(stringType())],
                'd' => ['type' => $dateType]
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $TestType = newObjectType([
            'name'   => 'TestType',
            'fields' => [
                'fieldWithObjectInput' => $this->fieldWithInputArg(['type' => $TestInputObject]),
            ]
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        $schema = newSchema([
            'query' => $TestType
        ]);

        $this->assertQueryResultWithSchema(
            $schema,
            '{
              fieldWithObjectInput(input: {c: "foo", d: "2018-01-01"})
            }',
            [
                'data' => [
                    'fieldWithObjectInput' => '{"c":"foo","d":{"date":"2018-01-01 00:00:00.000000","timezone_type":3,"timezone":"Europe\/Helsinki"}}'
                ]
            ],
        );
    }

    /**
     * @noinspection PhpDocMissingThrowsInspection
     *
     * @param string $query
     * @param array  $expected
     * @param array  $variables
     */
    protected function assertQueryResult(string $query, array $expected, array $variables = [])
    {
        $this->assertQueryResultWithSchema($this->schema, $query, $expected, null, null, $variables);
    }
}
