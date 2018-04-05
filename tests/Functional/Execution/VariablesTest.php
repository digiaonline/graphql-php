<?php

namespace Digia\GraphQL\Test\Functional\Execution;


use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\Language\dedent;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\newInputObjectType;
use function Digia\GraphQL\Type\newList;
use function Digia\GraphQL\Type\newNonNull;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newScalarType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\String;


/**
 * Class VariablesTest
 * @package Digia\GraphQL\Test\Functional\Execution
 */
class VariablesTest extends TestCase
{
    private $schema;


    /**
     * @throws \Digia\GraphQL\Error\InvalidTypeException
     */
    protected function setUp()
    {
        parent::setUp();

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
            'parseLiteral' => function ($ast) {
                if ($ast->getValue() === 'SerializedValue') {
                    return 'DeserializedValue';
                }
                return null;
            }
        ]);

        $TestInputObject = newInputObjectType([
            'name'   => 'TestInputObject',
            'fields' => [
                'a' => ['type' => String()],
                'b' => ['type' => newList(String())],
                'c' => ['type' => newNonNull(String())],
                'd' => ['type' => $TestComplexScalar]
            ]
        ]);

        $TestNestedInputObject = newInputObjectType([
            'name'   => 'TestNestedInputObject',
            'fields' => [
                'na' => ['type' => newNonNull($TestInputObject)],
                'nb' => ['type' => newNonNull(String())]
            ]
        ]);

        $TestType = newObjectType([
            'name'   => 'TestField',
            'fields' => [
                'fieldWithObjectInput'            => $this->fieldWithInputArg(['type' => $TestInputObject]),
                'fieldWithNullableStringInput'    => $this->fieldWithInputArg(['type' => String()]),
                'fieldWithNonNullableStringInput' => $this->fieldWithInputArg([
                    'type' => newNonNull(String())
                ]),
                'fieldWithDefaultArgumentValue'   => $this->fieldWithInputArg([
                    'type'         => String(),
                    'defaultValue' => 'Hello World'
                ]),
                'fieldWithNestedInputObject'      => $this->fieldWithInputArg([
                    'type'         => $TestNestedInputObject,
                    'defaultValue' => 'Hello World'
                ]),
                'list'                            => $this->fieldWithInputArg([
                    'type' => newList(String())
                ]),
                'nnList'                          => $this->fieldWithInputArg([
                    'type' => newNonNull(newList(String())),
                ]),
                'listNN'                          => $this->fieldWithInputArg([
                    'type' => newList(newNonNull(String())),
                ]),
                'nnListNN'                        => $this->fieldWithInputArg([
                    'type' => newNonNull(newList(newNonNull(String()))),
                ]),
            ]
        ]);

        $this->schema = newSchema([
            'query' => $TestType
        ]);
    }

    /**
     * @param $inputArg
     * @return array
     */
    function fieldWithInputArg($inputArg): array
    {
        return [
            'type'    => String(),
            'args'    => [
                'input' => $inputArg
            ],
            'resolve' => function ($root, $args) {
                if (isset($args['input'])) {
                    return json_encode($args['input']);
                }
            }
        ];
    }

    //Execute: Handles inputs

    //Handles objects and nullability

    /**
     * Executes with complex input
     *
     * @return \Digia\GraphQL\Execution\ExecutionResult
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testExecutesWithComplexInput()
    {
        $query = '{
            fieldWithObjectInput(input: {a: "foo", b: ["bar"], c: "baz"})
        }';

        $result = execute($this->schema, parse($query));

        $this->assertEquals([
            'data' => [
                'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}'
            ]
        ], $result->toArray());
    }


    /**
     * Properly parses single value to list
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testProperlyParsesSingleValueToList()
    {
        $query = '{
            fieldWithObjectInput(input: {a: "foo", b: "bar", c: "baz"})
        }';

        $result = execute($this->schema, parse($query));

        $this->assertEquals([
            'data' => [
                'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}'
            ]
        ], $result->toArray());
    }

    /**
     * Properly parses null value to null
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testProperlyParsesNullValueToNull()
    {
        $query = '{
            fieldWithObjectInput(input: {a: null, b: null, c: "C", d: null})
        }';

        $result = execute($this->schema, parse($query));

        $this->assertEquals([
            'data' => [
                'fieldWithObjectInput' => '{"a":null,"b":null,"c":"C","d":null}'
            ]
        ], $result->toArray());
    }

    /**
     * Properly parses null value in list
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testProperlyParsesNullValueInList()
    {
        $query = '{
            fieldWithObjectInput(input: {b: ["A",null,"C"], c: "C"})
        }';

        $result = execute($this->schema, parse($query));

        $this->assertEquals([
            'data' => [
                'fieldWithObjectInput' => '{"b":["A",null,"C"],"c":"C"}'
            ]
        ], $result->toArray());
    }

    /**
     * Does not use incorrect value
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testDoesNotUseIncorrectValue()
    {
        $query = '{
            fieldWithObjectInput(input: ["foo", "bar", "baz"])
        }';

        $result = execute($this->schema, parse($query));

        $this->assertEquals([
            'data'   => [
                'fieldWithObjectInput' => null
            ],
            'errors' => [
                [
                    //@TODO Check if line and column are correct
                    //Original message: Argument "input" has invalid value ["foo", "bar", "baz"].
                    'message'   => 'Input object values can only be resolved form object value nodes.',
                    'path'      => ['fieldWithObjectInput'],
                    'locations' => [['line' => 2, 'column' => 13]],
                ]
            ]
        ], $result->toArray());
    }

    /**
     * Properly runs parseLiteral on complex scalar types
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testProperlyRunsParseLiteralOnComplexScalarTypes()
    {
        $query = '{
            fieldWithObjectInput(input: {c: "foo", d: "SerializedValue"})
        }';

        $result = execute($this->schema, parse($query));

        $this->assertEquals([
            'data' => [
                'fieldWithObjectInput' => '{"c":"foo","d":"DeserializedValue"}'
            ]
        ], $result->toArray());
    }

    //USING VARIABLES

    /**
     * Executes with complex input
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testExecutesWithComplexInputUsingVariables()
    {
        $query = 'query ($input: TestInputObject) {
          fieldWithObjectInput(input: $input)
        }';

        $params = ['input' => ['a' => 'foo', 'b' => ['bar'], 'c' => 'baz']];

        $result = execute($this->schema, parse($query), null, null, $params);

        $this->assertEquals([
            'data' => [
                'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}'
            ]
        ], $result->toArray());
    }

    /**
     * Use default value when not provided
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testUseDefaultValueWhenNotProvide()
    {
        $query = 'query ($input: TestInputObject = {a: "foo", b: ["bar"], c: "baz"}) {
            fieldWithObjectInput(input: $input)
         }';

        $result = execute($this->schema, parse($query));

        $this->assertEquals([
            'data' => [
                'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}'
            ]
        ], $result->toArray());
    }

    /**
     * Properly parses single value to list
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testProperlyParsesSingleValueToListUsingVariable()
    {
        $params = ['input' => ['a' => 'foo', 'b' => 'bar', 'c' => 'baz']];

        $query = 'query ($input: TestInputObject) {
            fieldWithObjectInput(input: $input)
        }';

        $result = execute($this->schema, parse($query), null, null, $params);

        $this->assertEquals([
            'data' => [
                'fieldWithObjectInput' => '{"a":"foo","b":["bar"],"c":"baz"}'
            ]
        ], $result->toArray());
    }

    /**
     * Executes with complex scalar input using variable
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testExecutesWithComplexScalarInputUsingVariable()
    {
        $params = ['input' => ['c' => 'foo', 'd' => 'SerializedValue']];

        $query = 'query ($input: TestInputObject) {
            fieldWithObjectInput(input: $input)
        }';

        $result = execute($this->schema, parse($query), null, null, $params);

        $this->assertEquals([
            'data' => [
                'fieldWithObjectInput' => '{"c":"foo","d":"DeserializedValue"}'
            ]
        ], $result->toArray());
    }

    /**
     * Errors on null for nested non-null using variable
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testErrorsOnNullForNestedNonNullUsingVariable()
    {
        $params = ['input' => ['a' => 'foo', 'b' => 'bar', 'c' => null]];

        $query = 'query ($input: TestInputObject) {
            fieldWithObjectInput(input: $input)
        }';

        $result = execute($this->schema, parse(dedent($query)), null, null, $params);

        $this->assertEquals([
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
        ], $result->toArray());
    }

    /**
     * Errors on deep nested errors and with many errors
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testErrorsOnDeepNestedErrorsWithManyErrors()
    {
        $params = ['input' => ['na' => ['a' => 'foo']]];

        $query = 'query ($input: TestNestedInputObject) {
            fieldWithNestedObjectInput(input: $input)
         }';

        $result = execute($this->schema, parse(dedent($query)), null, null, $params);

        $this->assertEquals([
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
        ], $result->toArray());
    }

    /**
     * Errors on addition of unknown input field
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testErrorsOnAdditionOfUnknownInputField()
    {
        $params = ['input' => ['a' => 'foo', 'b' => 'bar', 'c' => 'baz', 'extra' => 'dog']];

        $query = 'query ($input: TestInputObject) {
            fieldWithObjectInput(input: $input)
        }';

        $result = execute($this->schema, parse(dedent($query)), null, null, $params);

        $this->assertEquals([
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
        ], $result->toArray());
    }

    // HANDLES NULLABLE SCALARS

    /**
     * Allows nullable inputs to be omitted
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsNullableInputsToBeOmitted()
    {
        $query = '{
          fieldWithNullableStringInput
        }';

        $result = execute($this->schema, parse(dedent($query)));

        $this->assertEquals([
            'data' => [
                'fieldWithNullableStringInput' => null
            ],
        ], $result->toArray());
    }

    /**
     * Allows nullable inputs to be omitted in a variable
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsNullableInputsToBeOmittedInAVariable()
    {
        $query = 'query ($value: String) {
          fieldWithNullableStringInput(input: $value)
        }';

        $result = execute($this->schema, parse(dedent($query)));

        $this->assertEquals([
            'data' => [
                'fieldWithNullableStringInput' => null
            ],
        ], $result->toArray());
    }


    /**
     * Allows nullable inputs to be omitted in an unlisted variable
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsNullableInputsToBeOmittedInUnlistedVariable()
    {
        $query = 'query {
          fieldWithNullableStringInput(input: $value)
        }';

        $result = execute($this->schema, parse(dedent($query)));

        $this->assertEquals([
            'data' => [
                'fieldWithNullableStringInput' => null
            ],
        ], $result->toArray());
    }

    /**
     * Allows nullable inputs to be set to null in a variable
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsNullableInputsToBeSetToNullInVariable()
    {
        $query = 'query ($value: String) {
          fieldWithNullableStringInput(input: $value)
        }';

        $result = execute($this->schema, parse(dedent($query)), null, null, ['value' => null]);

        $this->assertEquals([
            'data' => [
                'fieldWithNullableStringInput' => null
            ],
        ], $result->toArray());
    }

    /**
     * Allows nullable inputs to be set to a value directly
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsNullableInputsToBeSetToAValueDirectly()
    {
        $query = 'query ($value: String) {
          fieldWithNullableStringInput(input: "a")
        }';

        $result = execute($this->schema, parse(dedent($query)));

        $this->assertEquals([
            'data' => [
                'fieldWithNullableStringInput' => '"a"'
            ],
        ], $result->toArray());
    }

    // HANDLES NON-NULLABLE SCALARS'

    /**
     * Allows non-nullable inputs to be omitted given a default
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsNonNullableInputsToBeOmittedGivenADefault()
    {
        $query = 'query ($value: String = "default") {
          fieldWithNonNullableStringInput(input: $value)
        }';

        $result = execute($this->schema, parse(dedent($query)));

        $this->assertEquals([
            'data' => [
                'fieldWithNonNullableStringInput' => '"default"'
            ],
        ], $result->toArray());
    }

    /**
     * Does not allow non-nullable inputs to be omitted in a variable
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testDoesNotAllowNonNullableInputsToBeOmittedInAVariable()
    {
        $query = 'query ($value: String!) {
          fieldWithNonNullableStringInput(input: $value)
        }';

        $result = execute($this->schema, parse(dedent($query)));

        $this->assertEquals([
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
        ], $result->toArray());
    }


    /**
     * Does not allow non-nullable inputs to be set to null in a variable
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testDoesNotAllowNonNullabeInputsToBeSetToNullInAVariable()
    {
        $query = 'query ($value: String!) {
          fieldWithNonNullableStringInput(input: $value)
        }';

        $result = execute($this->schema, parse(dedent($query)), null, null, ['value' => null]);

        $this->assertEquals([
            'data'   => null,
            'errors' => [
                [
                    'message'   => 'Variable "$value" got invalid value null; ' .
                        'Expected non-nullable type String! not to be null.',
                    'locations' => [
                        [
                            'column' => 8,
                            'line'   => 1
                        ]
                    ],
                    'path'      => []
                ]
            ],
        ], $result->toArray());
    }


    /**
     * Allows non-nullable inputs to be set to a value in a variable
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsNonNullableInputsToBeSetToAValueInAVariable()
    {
        $query = 'query ($value: String!) {
          fieldWithNonNullableStringInput(input: $value)
        }';

        $result = execute($this->schema, parse(dedent($query)), null, null, ['value' => 'a']);

        $this->assertEquals([
            'data' => [
                'fieldWithNonNullableStringInput' => '"a"'
            ]
        ], $result->toArray());
    }

    /**
     * Allows non-nullable inputs to be set to a value directly
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsNonNullableInputsToBeSetToAValueDirectly()
    {
        $query = '{
          fieldWithNonNullableStringInput(input: "a")
        }';

        $result = execute($this->schema, parse(dedent($query)));

        $this->assertEquals([
            'data' => [
                'fieldWithNonNullableStringInput' => '"a"'
            ]
        ], $result->toArray());
    }

    /**
     * Reports error for missing non-nullable inputs
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testReportErrorForMissingNonNullableInputs()
    {
        $query = '{ fieldWithNonNullableStringInput }';

        $result = execute($this->schema, parse(dedent($query)));

        $this->assertEquals([
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
        ], $result->toArray());
    }

    /**
     * Reports error for array passed into string input
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testReportErrorForArrayPassedIntoStringInput()
    {
        $query = 'query ($value: String!) {
          fieldWithNonNullableStringInput(input: $value)
        }';

        $result = execute($this->schema, parse(dedent($query)), null, null, ['value' => [1, 2, 3]]);

        $this->assertEquals([
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
        ], $result->toArray());
    }

    //serializing an array via GraphQLString throws TypeError
    public function testSerializingAnArrayViaGraphQLStringThrowsTypeError()
    {
        $this->expectException(InvalidTypeException::class);

        String()->serialize([1, 2, 3]);
    }


    /**
     * Reports error for non-provided variables for non-nullable inputs
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testReportErrorForNonProvidedVariableForNonNullableInputs()
    {
        // Note: this test would typically fail validation before encountering
        // this execution error, however for queries which previously validated
        // and are being run against a new schema which have introduced a breaking
        // change to make a formerly non-required argument required, this asserts
        // failure before allowing the underlying code to receive a non-null value.

        $query = '{
          fieldWithNonNullableStringInput(input: $foo)
        }';

        $result = execute($this->schema, parse(dedent($query)));

        $this->assertEquals([
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
                            'column' => 50
                        ]
                    ],
                    'path'      => ['fieldWithNonNullableStringInput']
                ]
            ]
        ], $result->toArray());
    }

    // HANDLES LISTS AND NULLABILITY

    /**
     * Allows lists to be null
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsListsToBeNull()
    {
        $query = 'query ($input: [String]) {
          list(input: $input)
        }';

        $result = execute($this->schema, parse(dedent($query)), null, null, ['input' => null]);

        $this->assertEquals([
            'data'   => [
                'list' => null
            ],
        ], $result->toArray());
    }

    /**
     * Allows lists to contain values
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsListsContainValues()
    {
        $query = 'query ($input: [String]) {
          list(input: $input)
        }';

        $result = execute($this->schema, parse(dedent($query)), null, null, ['input' => ['A']]);

        $this->assertEquals([
            'data'   => [
                'list' => '["A"]'
            ],
        ], $result->toArray());
    }


    /**
     * Allows lists to contain null
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testAllowsListsContainNull()
    {
        $query = 'query ($input: [String]) {
          list(input: $input)
        }';

        $result = execute($this->schema, parse(dedent($query)), null, null, ['input' => ['A', null, 'B']]);

        $this->assertEquals([
            'data'   => [
                'list' => '["A",null,"B"]'
            ],
        ], $result->toArray());
    }
}