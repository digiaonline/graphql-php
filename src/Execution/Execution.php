<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\Handler\ErrorHandlerInterface;
use Digia\GraphQL\Execution\Strategy\FieldCollector;
use Digia\GraphQL\Execution\Strategy\ParallelExecutionStrategy;
use Digia\GraphQL\Execution\Strategy\SerialExecutionStrategy;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Schema\Schema;
use React\Promise\PromiseInterface;
use function React\Promise\resolve;

class Execution implements ExecutionInterface
{
    /**
     * @inheritdoc
     */
    public function execute(
        Schema $schema,
        DocumentNode $documentNode,
        $rootValue = null,
        $contextValue = null,
        array $variableValues = [],
        ?string $operationName = null,
        ?callable $fieldResolver = null,
        ?ErrorHandlerInterface $errorHandler = null
    ): PromiseInterface {
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
                return resolve(new ExecutionResult(null, $context->getErrors()));
            }
        } catch (ExecutionException $error) {
            return resolve(new ExecutionResult(null, [$error]));
        }

        $fieldCollector = new FieldCollector($context);

        $data = $this->executeOperation($operationName, $context, $fieldCollector);

        if ($data instanceof PromiseInterface) {
            return $data->then(function ($resolvedData) use ($context) {
                return new ExecutionResult($resolvedData, $context->getErrors());
            });
        }

        if (null !== $errorHandler) {
            foreach ($context->getErrors() as $error) {
                $errorHandler->handleExecutionError($error, $context);
            }
        }

        return resolve(new ExecutionResult($data, $context->getErrors()));
    }

    /**
     * @param null|string      $operationName
     * @param ExecutionContext $context
     * @param FieldCollector   $fieldCollector
     * @return array|mixed|null|PromiseInterface
     */
    protected function executeOperation(
        ?string $operationName,
        ExecutionContext $context,
        FieldCollector $fieldCollector
    ) {
        $strategy = $operationName === 'mutation'
            ? new SerialExecutionStrategy($context, $fieldCollector)
            : new ParallelExecutionStrategy($context, $fieldCollector);

        $result = null;

        try {
            $result = $strategy->execute();
        } catch (ExecutionException $exception) {
            $context->addError($exception);
        } catch (\Throwable $exception) {
            $context->addError(
                new ExecutionException($exception->getMessage(), null, null, null, null, null, $exception)
            );
        }

        if ($result instanceof PromiseInterface) {
            return $result->then(null, function (ExecutionException $exception) use ($context) {
                $context->addError($exception);
                return resolve(null);
            });
        }

        return $result;
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
                if (null === $operationName && null !== $operation) {
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

        $coercedVariableValues = ValuesResolver::coerceVariableValues(
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
}
