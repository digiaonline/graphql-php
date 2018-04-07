<?php

namespace Digia\GraphQL\Test\Functional\Execution;

use Digia\GraphQL\Test\TestCase;
use function Digia\GraphQL\graphql;
use function Digia\GraphQL\Type\Int;
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

    /**
     * Default function calls methods
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testDefaultFunctionCallsMethods()
    {
        $schema = $this->createTestSchema(['type' => String()]);

        $source = [
            'test' => function () {
                return 'secretValue';
            }
        ];

        $result = graphql($schema, '{ test }', $source);

        $this->assertEquals([
            'data' => [
                'test' => 'secretValue'
            ]
        ], $result);
    }

    /**
     * Default function passes args and context
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testDefaultFunctionPassesArgsAndContext()
    {
        $schema = $this->createTestSchema([
            'type' => Int(),
            'args' => ['addend1' => ['type' => Int()]],
        ]);

        $source = new class(700)
        {
            private $num;

            public function __construct($num)
            {
                $this->num = $num;
            }

            public function test($source, $args, $context)
            {
                return $this->num + $args['addend1'] + $context['addend2'];
            }
        };

        $result = graphql($schema, '{ test(addend1: 80) }', $source, ['addend2' => 9]);

        $this->assertEquals([
            'data' => [
                'test' => 789
            ]
        ], $result);
    }

    /**
     * Uses provided resolve function
     *
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function testUsesProviedResolveFunction()
    {
        $schema = $this->createTestSchema([
            'type'    => String(),
            'args'    => [
                'aStr' => ['type' => String()],
                'aInt' => ['type' => Int()],
            ],
            'resolve' => function ($source, $args) {
                return json_encode([$source, $args]);
            }
        ]);

        $this->assertEquals([
            'data' => [
                'test' => '[null,[]]'
            ]
        ], graphql($schema, '{ test }'));


        $this->assertEquals([
            'data' => [
                'test' => '["Source!",[]]'
            ]
        ], graphql($schema, '{ test }', 'Source!'));


        $this->assertEquals([
            'data' => [
                'test' => '["Source!",{"aStr":"String!"}]'
            ]
        ], graphql($schema, '{ test(aStr:"String!") }', 'Source!'));


        $this->assertEquals([
            'data' => [
                'test' => '["Source!",{"aStr":"String!","aInt":-123}]'
            ]
        ], graphql($schema, '{ test(aInt: -123, aStr: "String!") }', 'Source!'));
    }
}