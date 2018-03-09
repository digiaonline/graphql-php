<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
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
     * @throws GraphQLError
     * @return ExecutionContext
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
                        throw new GraphQLError(
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
                    throw new GraphQLError(
                        "GraphQL cannot execute a request containing a {$definition->getKind()}."
                    );
            }
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