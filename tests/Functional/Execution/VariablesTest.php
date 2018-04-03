<?php

namespace Digia\GraphQL\Test\Functional\Execution;


use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
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
}