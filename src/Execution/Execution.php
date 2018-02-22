<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Type\Definition\ObjectType;
use Digia\GraphQL\Type\Schema\Schema;

/**
 * Class Execution
 * @package Digia\GraphQL\Execution
 */
class Execution
{
    /**
     * @var ExecutionContext
     */
    protected $context;

    /**
     * Execution constructor.
     * @param ExecutionContext $context
     */
    public function __construct(ExecutionContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param Schema $schema
     * @param DocumentNode $documentNode
     * @param null $rootValue
     * @param null $contextValue
     * @param null $variableValues
     * @param null $operationName
     * @param callable|null $fieldResolver
     * @return ExecutionResult
     */
    public static function execute(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue = null,
        $contextValue = null,
        $variableValues = null,
        $operationName = null,
        callable $fieldResolver = null
    )
    {
        try {
            $context = self::buildExecutionContext(
                $schema,
                $documentNode,
                $rootValue,
                $contextValue,
                $variableValues,
                $operationName,
                $fieldResolver
            );
        } catch (GraphQLError $error) {
            return new ExecutionResult(null, [$error]);
        }

        $executor = new self($context);

        return $executor->executeOperation(
            $context,
            $context->getOperation(),
            $rootValue
        );
    }

    /**
     * @param Schema $schema
     * @param DocumentNode $documentNode
     * @param $rootValue
     * @param $contextValue
     * @param $rawVariableValues
     * @param null $operationName
     * @param callable|null $fieldResolver
     * @throws GraphQLError
     * @return ExecutionContext
     */
    private static function buildExecutionContext(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue,
        $contextValue,
        $rawVariableValues,
        $operationName = null,
        callable $fieldResolver = null
    ): ExecutionContext
    {
        //TODO: Validate raw variables, operation name etc.
        //TODO: Validate document definition

        $executionContext = new ExecutionContext(
            $schema,
            null,
            $rootValue,
            $contextValue,
            $rawVariableValues,
            $fieldResolver,
            $operationName,
            []
        );

        return $executionContext;
    }

    /**
     * @param ExecutionContext $context
     * @param OperationDefinitionNode $operation
     * @param $rootValue
     *
     * @return ExecutionResult
     */
    private function executeOperation(
        ExecutionContext $context,
        OperationDefinitionNode $operation,
        $rootValue
    ): ExecutionResult
    {
        //MUTATION
        //SUBSCRIPTION
        //QUERY

        //result = executionStrategy.execute(executionContext, parameters);
        //return result
        return new ExecutionResult([], []);
    }

    private function executeFieldsSerially(ObjectType $parentType, $sourceValue, $path, $fields)
    {

    }

    /**
     * Implements the "Evaluating selection sets" section of the spec
     * for "read" mode.
     * @param ObjectType $parentType
     * @param $source
     * @param $path
     * @param $fields
     */
    private function executeFields(ObjectType $parentType, $source, $path, $fields)
    {
        $finalResults = [];

        foreach ($fields as $responseName => $fieldNodes) {
            $fieldPath = $path;
            $fieldPath[] = $responseName;
            $result = $this->resolveField($parentType, $source, $fieldNodes, $fieldPath);
            $finalResults[$responseName] = $result;
        }
    }

    /**
     * @param ObjectType $parentType
     * @param $source
     * @param $fieldNodes
     * @param $path
     *
     * @return array|\Exception|mixed|null
     */
    private function resolveField(ObjectType $parentType, $source, $fieldNodes, $path)
    {

    }
}
