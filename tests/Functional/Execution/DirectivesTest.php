<?php

namespace Digia\GraphQL\Test\Functional\Execution;


use function Digia\GraphQL\execute;
use function Digia\GraphQL\parse;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;

class DirectivesTest extends TestCase
{
    /**
     * @var Schema
     */
    private $schema;

    /**
     * @var array
     */
    private $data = [
        'a' => 'a',
        'b' => 'b'
    ];

    public function setUp()
    {
        parent::setUp();

        $this->schema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'TestType',
                'fields' => [
                    'a' => ['type' => GraphQLString()],
                    'b' => ['type' => GraphQLString()]
                ]
            ])
        ]);
    }

    /**
     * @throws \Digia\GraphQL\Error\InvariantException
     * @throws \Digia\GraphQL\Error\SyntaxErrorException
     */
    public function testWorksWithDirective()
    {
        $result = execute($this->schema, parse('{ a, b }'), $this->data);

        $this->assertEquals($this->data, $result->getData());
    }
}