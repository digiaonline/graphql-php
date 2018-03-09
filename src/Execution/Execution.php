<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Type\Schema;
use function Digia\GraphQL\Util\invariant;

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
     * @param Schema        $schema
     * @param DocumentNode  $documentNode
     * @param null          $rootValue
     * @param null          $contextValue
     * @param null          $variableValues
     * @param null          $operationName
     * @param callable|null $fieldResolver
     * @return ExecutionResult
     */
    public static function execute(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue = null,
        $contextValue = null,
        $variableValues = [],
        $operationName = null,
        callable $fieldResolver = null
    ) {
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
