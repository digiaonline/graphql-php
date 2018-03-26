<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Type\Schema;

/**
 * Class ExecutionContextBuilder
 * @package Digia\GraphQL\Execution
 */
class ExecutionContextBuilder
{
    /**
     * @param Schema        $schema
     * @param DocumentNode  $documentNode
     * @param               $rootValue
     * @param               $contextValue
     * @param               $rawVariableValues
     * @param null          $operationName
     * @param callable|null $fieldResolver
     * @return ExecutionContext
     * @throws ExecutionException
     */
    public function buildContext(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue,
        $contextValue,
        $rawVariableValues,
        $operationName = null,
        callable $fieldResolver = null
    ): ExecutionContext {
        //@TODO validate $rawVariableValues?

        $errors    = [];
        $fragments = [];
        $operation = null;

        foreach ($documentNode->getDefinitions() as $definition) {
            switch ($definition->getKind()) {
                case NodeKindEnum::OPERATION_DEFINITION:
                    if (!$operationName && $operation) {
                        throw new ExecutionException(
                            'Must provide operation name if query contains multiple operations.'
                        );
                    }

                    if (!$operationName || (!empty($definition->getName()) && $definition->getName()->getValue() === $operationName)) {
                        $operation = $definition;
                    }
                    break;
                case NodeKindEnum::FRAGMENT_DEFINITION:
                case NodeKindEnum::FRAGMENT_SPREAD:
                    $fragments[$definition->getName()->getValue()] = $definition;
                    break;
                default:
                    throw new ExecutionException(
                        "GraphQL cannot execute a request containing a {$definition->getKind()}."
                    );
            }
        }

        if(null === $operation) {
            throw new ExecutionException('Must provide an operation.');
        }

        $executionContext = new ExecutionContext(
            $schema,
            $fragments,
            $rootValue,
            $contextValue,
            $rawVariableValues,
            $fieldResolver,
            $operation,
            $errors
        );

        return $executionContext;
    }
}
