<?php

namespace Digia\GraphQL\Execution\Strategies;

use Digia\GraphQL\Execution\ExecutionResult;

/**
 * Class MutationStrategy
 * @package Digia\GraphQL\Execution\Strategies
 */
class MutationStrategy extends AbstractStrategy
{

    /**
     * @return ExecutionResult
     */
    public function execute(): ExecutionResult
    {
        return new ExecutionResult([], []);
    }
}
