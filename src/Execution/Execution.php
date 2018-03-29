<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Type\Schema;

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
            $context = $this->contextBuilder->buildContext(
                $schema,
                $documentNode,
                $rootValue,
                $contextValue,
                $variableValues,
                $operationName,
                $fieldResolver
            );
        } catch (ExecutionException $error) {
            return new ExecutionResult(null, [$error]);
        }

        $data = $context->getExecutionStrategy()->execute();

        return new ExecutionResult($data, $context->getErrors());
    }
}
