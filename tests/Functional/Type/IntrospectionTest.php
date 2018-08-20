<?php

namespace Digia\GraphQL\Test\Functional\Type;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\stringType;
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
        $emptySchema = newSchema([
            'query' => newObjectType([
                'name'   => 'QueryRoot',
                'fields' => [
                    'onlyField' => ['type' => stringType()],
                ],
            ]),
        ]);

        /** @noinspection PhpUnhandledExceptionInspection */
        graphql($emptySchema, $this->introspectionQuery);

        $this->addToAssertionCount(1);
    }
}
