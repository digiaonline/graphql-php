<?php

namespace Digia\GraphQL\Execution\Strategies;

use Digia\GraphQL\Execution\ExecutionResult;

/**
 * Class SubscriptionStrategy
 * @package Digia\GraphQL\Execution\Strategies
 */
class SubscriptionStrategy extends AbstractStrategy
{

    /**
     * @return ExecutionResult
     */
    public function execute(): ExecutionResult
    {
        return new ExecutionResult([], []);
    }
}
