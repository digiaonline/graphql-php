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
                'fieldWithObjectInput'            => fieldWithInputArg(['type' => $TestInputObject]),
                'fieldWithNullableStringInput'    => fieldWithInputArg(['type' => GraphQLString()]),
                'fieldWithNonNullableStringInput' => fieldWithInputArg([
                    'type' => newGraphQLNonNull(GraphQLString())
                ]),
                'fieldWithDefaultArgumentValue'   => fieldWithInputArg([
                    'type'         => GraphQLString(),
                    'defaultValue' => 'Hello World'
                ]),
                'fieldWithNestedInputObject'      => fieldWithInputArg([
                    'type'         => $TestNestedInputObject,
                    'defaultValue' => 'Hello World'
                ]),
                'list'                            => fieldWithInputArg(['type' => newGraphQLList(GraphQLString())]),
                'nnList'                          => fieldWithInputArg([
                    'type' => newGraphQLNonNull(newGraphQLList(GraphQLString())),
                ]),
                'listNN'                          => fieldWithInputArg([
                    'type' => newGraphQLList(newGraphQLNonNull(GraphQLString())),
                ]),
                'nnListNN'                        => fieldWithInputArg([
                    'type' => newGraphQLNonNull(newGraphQLList(newGraphQLNonNull(GraphQLString()))),
                ]),
            ]
        ]);

        $this->schema = newGraphQLSchema([
            'query' => $TestType
        ]);
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
}