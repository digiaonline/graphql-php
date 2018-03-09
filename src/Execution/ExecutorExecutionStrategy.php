<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;

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

        $path = [];

        try {
            $fields = $this->collectFields($objectType, $this->operation->getSelectionSet(), new \ArrayObject(),
                new \ArrayObject());

            $data = $this->executeFields($objectType, $this->rootValue, $path, $fields);

        } catch (\Exception $ex) {
            $this->context->addError(
                new ExecutionException($ex->getMessage())
            );
            return [$ex->getMessage()];
        }

        return $data;
    }
}
