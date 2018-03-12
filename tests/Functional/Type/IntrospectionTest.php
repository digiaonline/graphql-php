<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Type\GraphQLObjectType;
use function Digia\GraphQL\Type\GraphQLSchema;
use function Digia\GraphQL\Type\GraphQLString;
use function Digia\GraphQL\Util\readFile;

class IntrospectionTest extends TestCase
{

    /**
     * @var string
     */
    protected $introspectionQuery;

    public function setUp()
    {
        $this->introspectionQuery = readFile(__DIR__ . '/../../../resources/introspection.graphql');
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

        graphql($emptySchema, $this->introspectionQuery);

        $this->addToAssertionCount(1);
    }
}
