<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\DocumentNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
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

        $errors = [];
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
                    $fragments[$definition->getName()->getValue()] = $definition;
                    break;
                default:
                    throw new GraphQLError(
                        "GraphQL cannot execute a request containing a {$definition->getKind()}.",
                        [$definition]
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
        // type: query|mutation|suscription
        $query  = $context->getSchema()->getQuery();
        $fields = $this->collectFields($query, $operation->getSelectionSet(), [], []);
        $path   = [];

        if ($context->getOperation()->getName()->getValue() === 'query') {
            $data = $this->executeFields($query, $rootValue, $path, $fields);

            return new ExecutionResult($data, []);
        }

        return new ExecutionResult([], []);
    }

    private function collectFields(
        ObjectType $runtimeType,
        SelectionSetNode $selectionSet,
        $fields,
        $visitedFragmentNames
    )
    {
        foreach ($selectionSet->getSelections() as $selection) {
            switch ($selection->getKind()) {
                case NodeKindEnum::FIELD:
                    /** @var FieldNode $selection */
                    $name = $selection->getName()->getValue();
                    $fields[$name][] = $selection;
                    break;
            }
        }

        return $fields;
    }

    /**
     * Implements the "Evaluating selection sets" section of the spec
     * for "read" mode.
     * @param ObjectType $parentType
     * @param $source
     * @param $path
     * @param $fields
     *
     * @return array
     */
    private function executeFields(ObjectType $parentType, $source, $path, $fields): array
    {
        $finalResults = [];
        foreach ($fields as $responseName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $responseName;

            $result = $this->resolveField($parentType, $source, $fieldNodes, $fieldPath);

            $finalResults[$responseName] = $result;
        }

        return $finalResults;
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
        /** @var FieldNode $fieldNode */
        $fieldNode = $fieldNodes[0];

        $field = $parentType->getFields()[$fieldNode->getName()->getValue()];

        $inputValues = $fieldNode->getArguments() ?? [];

        $args = [];

        foreach($inputValues as $value) {
            $args[] = $value->getDefaultValue();
        }

        return $field->resolve(...$args);
    }
}
