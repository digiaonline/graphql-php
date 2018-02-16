<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Type\Schema\Schema;

/**
 * Class Executor
 * @package Digia\GraphQL\Execution
 */
class Executor
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Executor constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
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
     * @return Result
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
            $context = self::buildExecutionContext($schema, $documentNode, $rootValue, $contextValue, $variableValues,
                $operationName, $fieldResolver);
        } catch (GraphQLError $error) {
            return new Result(null, [$error]);
        }

        $executor = new self($context);

        return $executor->executeOperation($context, $context->getOperation(), $rootValue);
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
     * @return Context
     */
    private static function buildExecutionContext(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue,
        $contextValue,
        $rawVariableValues,
        $operationName = null,
        callable $fieldResolver = null) : Context
    {

    }

    /**
     * @param Context $context
     * @param OperationDefinitionNode $operation
     * @param $rootValue
     *
     * @return Result
     */
    private function executeOperation(Context $context, OperationDefinitionNode $operation, $rootValue): Result
    {
        return new Result([], []);
    }
}
