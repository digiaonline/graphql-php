<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\Handler\ErrorHandlerInterface;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\Schema;
use React\Promise\PromiseInterface;

interface ExecutionInterface
{
    /**
     * @param Schema                     $schema
     * @param DocumentNode               $documentNode
     * @param mixed                      $rootValue
     * @param mixed                      $contextValue
     * @param array                      $variableValues
     * @param string|null                $operationName
     * @param callable|null              $fieldResolver
     * @param ErrorHandlerInterface|null $errorHandler
     * @return PromiseInterface<ExecutionResult>
     */
    public function execute(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue = null,
        $contextValue = null,
        array $variableValues = [],
        string $operationName = null,
        callable $fieldResolver = null,
        ?ErrorHandlerInterface $errorHandler = null
    ): PromiseInterface;
}
