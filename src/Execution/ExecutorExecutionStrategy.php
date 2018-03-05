<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;

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

        $fields = $this->collectFields($objectType, $this->operation->getSelectionSet(), new \ArrayObject(), new \ArrayObject());

        $path   = [];

        try {
            $data = $this->executeFields($objectType, $this->rootValue, $path, $fields);
        } catch (\Exception $ex) {
            $this->context->addError(
                new GraphQLError($ex->getMessage())
            );
            return [$ex->getMessage()];
        }

        return $data;
    }
}
