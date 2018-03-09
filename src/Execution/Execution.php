<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Type\Schema;

/**
 * Class Execution
 * @package Digia\GraphQL\Execution
 */
class Execution implements ExecutionInterface
{
    /**
     * @param Schema        $schema
     * @param DocumentNode  $documentNode
     * @param null          $rootValue
     * @param null          $contextValue
     * @param null          $variableValues
     * @param null          $operationName
     * @param callable|null $fieldResolver
     * @return ExecutionResult
     */
    public function execute(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue = null,
        $contextValue = null,
        $variableValues = [],
        $operationName = null,
        callable $fieldResolver = null
    ) : ExecutionResult {
        try {
            //@TODO Get context builder from container?
            $contextBuilder = new ExecutionContextBuilder();

            $context = $contextBuilder->buildContext(
                $schema,
                $documentNode,
                $rootValue,
                $contextValue,
                $variableValues,
                $operationName,
                $fieldResolver
            );
        } catch (ExecutionException $error) {
            return new ExecutionResult(['data' => null], [$error]);
        }

        $data = $context->getExecutionStrategy()->execute();

        return new ExecutionResult($data, $context->getErrors());
    }
}
