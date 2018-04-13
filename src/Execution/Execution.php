<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\Schema;

/**
 * Class Execution
 * @package Digia\GraphQL\Execution
 */
class Execution implements ExecutionInterface
{
    /**
     * @var ExecutionContextBuilder
     */
    protected $contextBuilder;

    /**
     * Execution constructor.
     * @param $contextBuilder
     */
    public function __construct(ExecutionContextBuilder $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param Schema        $schema
     * @param DocumentNode  $documentNode
     * @param object|array  $rootValue
     * @param null          $contextValue
     * @param array         $variableValues
     * @param null          $operationName
     * @param callable|null $fieldResolver
     * @return ExecutionResult
     * @throws \Throwable
     */
    public function execute(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue = null,
        $contextValue = null,
        array $variableValues = [],
        string $operationName = null,
        callable $fieldResolver = null
    ): ExecutionResult {
        try {
            $context = $this->contextBuilder->buildContext(
                $schema,
                $documentNode,
                $rootValue,
                $contextValue,
                $variableValues,
                $operationName,
                $fieldResolver
            );

            // Return early errors if execution context failed.
            if (!empty($context->getErrors())) {
                return new ExecutionResult(null, $context->getErrors());
            }
        } catch (ExecutionException $error) {
            return new ExecutionResult(null, [$error]);
        }

        $data = $context->createExecutor()->execute();

        return new ExecutionResult($data, $context->getErrors());
    }
}
