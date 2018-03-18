<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use React\Promise\ExtendedPromiseInterface;

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
            ? $schema->getMutation()
            : $schema->getQuery();

        $fields = [];
        $visitedFragmentNames = [];
        try {
            $fields = $this->collectFields(
                $objectType,
                $this->operation->getSelectionSet(),
                $fields,
                $visitedFragmentNames
            );

            $result = ($operation === 'mutation')
                ? $this->executeFieldsSerially($objectType, $this->rootValue, $path, $fields)
                : $this->executeFields($objectType, $this->rootValue, $path, $fields);

        } catch (\Exception $ex) {
            $this->context->addError(
                new ExecutionException($ex->getMessage())
            );

            //@TODO return [null]
            return [$ex->getMessage()];
        }

        return $result;
    }
}
