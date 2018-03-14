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

        $path = [];

        $objectType = ($operation === 'mutation')
            ? $schema->getMutation()
            : $schema->getQuery();

        try {
            $fields = $this->collectFields(
                $objectType,
                $this->operation->getSelectionSet(),
                new \ArrayObject(),
                new \ArrayObject()
            );

            $data = ($operation === 'mutation')
                ? $this->executeFieldsSerially($objectType, $this->rootValue, $path, $fields)
                : $this->executeFields($objectType, $this->rootValue, $path, $fields);

        } catch (\Exception $ex) {
            $this->context->addError(
                new ExecutionException($ex->getMessage())
            );

            //@TODO return [null]
            return [$ex->getMessage()];
        }

        return $data;
    }
}
