<?php

namespace Digia\GraphQL\Test\Functional\Execution;


use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Type\newGraphQLInputObjectType;
use function Digia\GraphQL\Type\newGraphQLList;
use function Digia\GraphQL\Type\newGraphQLNonNull;
use function Digia\GraphQL\Type\newGraphQLObjectType;
use function Digia\GraphQL\Type\newGraphQLScalarType;
use function Digia\GraphQL\Type\newGraphQLSchema;


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

        $TestComplexScalar = newGraphQLScalarType([
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

        $TestInputObject = newGraphQLInputObjectType([
            'name'   => 'TestInputObject',
            'fields' => [
                'a' => ['type' => GraphQLString()],
                'b' => ['type' => newGraphQLList(GraphQLString())],
                'c' => ['type' => newGraphQLNonNull(GraphQLString())],
                'd' => ['type' => $TestComplexScalar]
            ]
        ]);

        $TestNestedInputObject = newGraphQLInputObjectType([
            'name'   => 'TestNestedInputObject',
            'fields' => [
                'na' => ['type' => newGraphQLNonNull($TestInputObject)],
                'nb' => ['type' => newGraphQLNonNull(GraphQLString())]
            ]
        ]);

        $TestType = newGraphQLObjectType([
            'name'   => 'TestField',
            'fields' => [
                'fieldWithObjectInput'            => $this->fieldWithInputArg(['type' => $TestInputObject]),
                'fieldWithNullableStringInput'    => $this->fieldWithInputArg(['type' => GraphQLString()]),
                'fieldWithNonNullableStringInput' => $this->fieldWithInputArg([
                    'type' => newGraphQLNonNull(GraphQLString())
                ]),
                'fieldWithDefaultArgumentValue'   => $this->fieldWithInputArg([
                    'type'         => GraphQLString(),
                    'defaultValue' => 'Hello World'
                ]),
                'fieldWithNestedInputObject'      => $this->fieldWithInputArg([
                    'type'         => $TestNestedInputObject,
                    'defaultValue' => 'Hello World'
                ]),
                'list'                            => $this->fieldWithInputArg([
                    'type' => newGraphQLList(GraphQLString())
                ]),
                'nnList'                          => $this->fieldWithInputArg([
                    'type' => newGraphQLNonNull(newGraphQLList(GraphQLString())),
                ]),
                'listNN'                          => $this->fieldWithInputArg([
                    'type' => newGraphQLList(newGraphQLNonNull(GraphQLString())),
                ]),
                'nnListNN'                        => $this->fieldWithInputArg([
                    'type' => newGraphQLNonNull(newGraphQLList(newGraphQLNonNull(GraphQLString()))),
                ]),
            ]
        ]);

        $this->schema = newGraphQLSchema([
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
            'type'    => GraphQLString(),
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