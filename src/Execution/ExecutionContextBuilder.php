<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeKindEnum;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Schema\Schema;

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
     * @throws \Exception
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
            }
        }

        if (null === $operation) {
            if ($operationName !== null) {
                throw new ExecutionException(sprintf('Unknown operation named "%s".', $operationName));
            }

            throw new ExecutionException('Must provide an operation.');
        }

        $variableValues = [];

        /** @var OperationDefinitionNode $operation */
        if ($operation) {
            $coercedVariableValues = (new ValuesHelper())->coerceVariableValues(
                $schema,
                $operation->getVariableDefinitions(),
                $rawVariableValues
            );

            $variableValues = $coercedVariableValues['coerced'];
        }

        $executionContext = new ExecutionContext(
            $schema,
            $fragments,
            $rootValue,
            $contextValue,
            $variableValues,
            $fieldResolver,
            $operation,
            $errors
        );

        return $executionContext;
    }
}
