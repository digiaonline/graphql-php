<?php

namespace Digia\GraphQL\Test\Unit\Execution;

use Digia\GraphQL\Error\GraphQLException;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Test\TestCase;

class ExecutionResultTest extends TestCase
{

    public function testBasics(): void
    {
        // Start with null data and no errors
        $executionResult = new ExecutionResult(null, []);

        $this->assertNull($executionResult->getData());
        $this->assertEmpty($executionResult->getErrors());
        $this->assertSame(['data' => null], $executionResult->toArray());

        // Start with data and no errors
        $executionResult = new ExecutionResult(['foo' => 'bar'], []);

        $this->assertEquals(['foo' => 'bar'], $executionResult->getData());
        $this->assertEmpty($executionResult->getErrors());
        $this->assertSame(['data' => ['foo' => 'bar']], $executionResult->toArray());

        // Start with data and an error
        $executionResult = new ExecutionResult(['foo' => 'bar'], [new GraphQLException('Error')]);

        $this->assertEquals(['foo' => 'bar'], $executionResult->getData());
        $this->assertCount(1, $executionResult->getErrors());
        $this->assertSame([
            'errors' => [
                [
                    'message'   => 'Error',
                    'locations' => null,
                ]
            ],
            'data'   => ['foo' => 'bar'],
        ], $executionResult->toArray());

        // Add another error
        $executionResult->addError(new GraphQLException('Another error'));
        $this->assertCount(2, $executionResult->getErrors());
        $this->assertSame([
            'errors' => [
                [
                    'message'   => 'Error',
                    'locations' => null
                ],
                [
                    'message'   => 'Another error',
                    'locations' => null
                ]
            ],
            'data'   => ['foo' => 'bar'],
        ], $executionResult->toArray());
    }
}
