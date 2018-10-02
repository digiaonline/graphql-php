<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\ErrorHandlerInterface;
use Digia\GraphQL\Error\ExecutionException;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Schema\Schema;

class Execution implements ExecutionInterface
{
    /**
     * @var ErrorHandlerInterface
     */
    private $errorHandler;

    /**
     * Execution constructor.
     * @param ErrorHandlerInterface $errorHandler
     */
    public function __construct(ErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * @param Schema        $schema
     * @param DocumentNode  $documentNode
     * @param mixed         $rootValue
     * @param mixed         $contextValue
     * @param array         $variableValues
     * @param null|string   $operationName
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
        ?string $operationName = null,
        ?callable $fieldResolver = null
    ): ExecutionResult {
        try {
            $context = $this->createContext(
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

        $data   = $this->createExecutor($context)->execute();
        $errors = $context->getErrors();

        return new ExecutionResult($data, $errors);
    }

    /**
     * @param Schema        $schema
     * @param DocumentNode  $documentNode
     * @param mixed         $rootValue
     * @param mixed         $contextValue
     * @param mixed         $rawVariableValues
     * @param null|string   $operationName
     * @param callable|null $fieldResolver
     * @return ExecutionContext
     * @throws ExecutionException
     * @throws \Exception
     */
    protected function createContext(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue,
        $contextValue,
        $rawVariableValues,
        ?string $operationName = null,
        ?callable $fieldResolver = null
    ): ExecutionContext {
        $errors    = [];
        $fragments = [];
        $operation = null;

        foreach ($documentNode->getDefinitions() as $definition) {
            if ($definition instanceof OperationDefinitionNode) {
                if (null === $operationName && $operation) {
                    throw new ExecutionException(
                        'Must provide operation name if query contains multiple operations.'
                    );
                }

                if (null === $operationName || $definition->getNameValue() === $operationName) {
                    $operation = $definition;
                }

                continue;
            }

            if ($definition instanceof FragmentDefinitionNode || $definition instanceof FragmentSpreadNode) {
                $fragments[$definition->getNameValue()] = $definition;

                continue;
            }
        }

        if (null === $operation) {
            if (null !== $operationName) {
                throw new ExecutionException(sprintf('Unknown operation named "%s".', $operationName));
            }

            throw new ExecutionException('Must provide an operation.');
        }

        $coercedVariableValues = coerceVariableValues(
            $schema,
            $operation->getVariableDefinitions(),
            $rawVariableValues
        );

        $variableValues = $coercedVariableValues->getValue();

        if ($coercedVariableValues->hasErrors()) {
            $errors = $coercedVariableValues->getErrors();
        }

        return new ExecutionContext(
            $schema,
            $fragments,
            $rootValue,
            $contextValue,
            $variableValues,
            $fieldResolver,
            $operation,
            $errors
        );
    }

    /**
     * @param ExecutionContext $context
     * @return Executor
     */
    protected function createExecutor(ExecutionContext $context): Executor
    {
        return new Executor($context, new FieldCollector($context), $this->errorHandler);
    }
}
