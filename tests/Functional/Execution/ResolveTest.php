<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Type\newObjectType;
use function Digia\GraphQL\Type\newSchema;
use function Digia\GraphQL\Type\String;

class ResolveTest extends TestCase
{

    protected function createTestSchema($testField)
    {
        return newSchema([
            'query' => newObjectType([
                'name'   => 'Query',
                'fields' => [
                    'test' => $testField
                ]
            ])
        ]);
    }

    // Execute: resolve function

    /**
     * Default function accesses properties
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testDefaultFunctionAccessProperties()
    {
        $schema = $this->createTestSchema(['type' => String()]);

        $source = ['test' => 'testValue'];

        $result = graphql($schema, '{ test }', $source);

        $this->assertEquals([
            'data' => [
                'test' => 'testValue'
            ]
        ], $result);
    }
}