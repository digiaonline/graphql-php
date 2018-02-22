<?php

namespace Digia\GraphQL\Test\Functional\Excecution;

use Digia\GraphQL\Execution\Execution;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Test\TestCase;
use Digia\GraphQL\Type\Schema\Schema;

class ExecutionTest extends TestCase
{
    public function testExecuteSchema()
    {
        $schema         = new Schema();
        $documentNode   = new DocumentNode();
        $rootValue      = '';
        $contextValue   = '';
        $variableValues = [];
        $operationName  = 'Query';
        $fieldResolver  = null;

        /** @var ExecutionResult $executionResult */
        $executionResult = Execution::execute(
            $schema,
            $documentNode,
            $rootValue,
            $contextValue,
            $variableValues,
            $operationName,
            $fieldResolver
        );

    }
}
