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
        $mutation  = $this->context->getSchema()->getMutation();
        $fields = $this->collectFields($mutation, $this->operation->getSelectionSet(), [], []);
        $path   = [];

        try {
            $data = $this->executeFields($mutation, $this->rootValue, $path, $fields);
        } catch (\Exception $ex) {
            return new ExecutionResult([], [$ex]);
        }

        return new ExecutionResult($data, []);
    }
}
