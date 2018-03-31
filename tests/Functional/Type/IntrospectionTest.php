<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Type\newGraphQLObjectType;
use function Digia\GraphQL\Type\newGraphQLSchema;
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
        $emptySchema = newGraphQLSchema([
            'query' => newGraphQLObjectType([
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
