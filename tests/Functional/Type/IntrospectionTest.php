<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Test\readFileContents;

class IntrospectionTest extends TestCase
{
    protected $introspectionQuery;

    public function setUp()
    {
        $this->introspectionQuery = readFileContents(__DIR__ . '/introspection.graphql');
    }

    public function testExecutesAnIntrospectionQuery()
    {
        $emptySchema = GraphQLSchema([
            'query' => GraphQLObjectType([
                'name'   => 'QueryRoot',
                'fields' => [
                    'onlyField' => ['type' => GraphQLString()],
                ],
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        graphql($emptySchema, $this->introspectionQuery);

        $this->addToAssertionCount(1);
    }
}
