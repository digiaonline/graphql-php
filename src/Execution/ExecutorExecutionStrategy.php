<?php

namespace Digia\GraphQL\Execution;

class ExecutorExecutionStrategy extends ExecutionStrategy
{

    /**
     * @return ?array
     */
    function execute(): ?array
    {
        $operation = $this->context->getOperation()->getOperation();
        $schema    = $this->context->getSchema();

        $objectType = ($operation === 'mutation')
            ? $schema->getMutation()
            : $schema->getQuery();

        $fields = $this->collectFields($objectType, $this->operation->getSelectionSet(), [], []);
        $path   = [];

        try {
            $data = $this->executeFields($objectType, $this->rootValue, $path, $fields);
        } catch (\Exception $ex) {
            $this->context->addError($ex);
            return null;
        }

        return $data;
    }
}
