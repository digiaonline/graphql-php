<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;

class ExecutorExecutionStrategy extends ExecutionStrategy
{

    /**
     * @return ?array
     * @throws \Throwable
     */
    function execute(): ?array
    {
        $operation = $this->context->getOperation()->getOperation();
        $schema    = $this->context->getSchema();

        $path = [];

        $objectType = ($operation === 'mutation')
            ? $schema->getMutationType()
            : $schema->getQueryType();

        $fields = [];
        $visitedFragmentNames = [];
        try {
            $fields = $this->collectFields(
                $objectType,
                $this->operation->getSelectionSet(),
                $fields,
                $visitedFragmentNames
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
