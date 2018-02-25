<?php

namespace Digia\GraphQL\Execution\Strategies;

use Digia\GraphQL\Execution\ExecutionResult;

/**
 * Class QueryStrategy
 * @package Digia\GraphQL\Execution\Strategies
 */
class QueryStrategy extends AbstractStrategy
{

    /**
     * @return ExecutionResult
     */
    public function execute(): ExecutionResult
    {
        $query  = $this->context->getSchema()->getQuery();
        $fields = $this->collectFields($query, $this->operation->getSelectionSet(), [], []);
        $path   = [];

        try {
            $data = $this->executeFields($query, $this->rootValue, $path, $fields);
        } catch (\Exception $ex) {
            return new ExecutionResult([], [$ex]);
        }

        return new ExecutionResult($data, []);
    }
}
