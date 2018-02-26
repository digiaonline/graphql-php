<?php

namespace Digia\GraphQL\Execution\Strategies;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ExecutionResult;
use Digia\GraphQL\Language\AST\Node\ArgumentNode;
use Digia\GraphQL\Language\AST\Node\Contract\ValueNodeInterface;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use Digia\GraphQL\Language\AST\Node\StringValueNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Type\Definition\ObjectType;

/**
 * Class AbstractStrategy
 * @package Digia\GraphQL\Execution\Strategies
 */
abstract class AbstractStrategy
{
    /**
     * @var ExecutionContext
     */
    protected $context;

    /**
     * @var OperationDefinitionNode
     */
    protected $operation;

    /**
     * @var mixed
     */
    protected $rootValue;

    /**
     * AbstractStrategy constructor.
     * @param ExecutionContext $context
     *
     * @param OperationDefinitionNode $operation
     */
    public function __construct(
        ExecutionContext $context,
        OperationDefinitionNode $operation,
        $rootValue)
    {
        $this->context   = $context;
        $this->operation = $operation;
        $this->rootValue = $rootValue;
    }

    abstract function execute(): ExecutionResult;

    /**
     * @param ObjectType $runtimeType
     * @param SelectionSetNode $selectionSet
     * @param $fields
     * @param $visitedFragmentNames
     * @return mixed
     */
    protected function collectFields(
        ObjectType $runtimeType,
        SelectionSetNode $selectionSet,
        $fields,
        $visitedFragmentNames
    ) {
        foreach ($selectionSet->getSelections() as $selection) {
            switch ($selection->getKind()) {
                case NodeKindEnum::FIELD:
                    /** @var FieldNode $selection */
                    $name            = $selection->getName()->getValue();
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
     *
     * @throws GraphQLError|\Exception
     */
    protected function executeFields(
        ObjectType $parentType,
        $source,
        $path,
        $fields): array
    {
        $finalResults = [];
        foreach ($fields as $responseName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $responseName;

            $result = $this->resolveField($parentType,
                $source,
                $fieldNodes,
                $fieldPath
            );

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
     * @return mixed
     *
     * @throws GraphQLError|\Exception
     */
    protected function resolveField(
        ObjectType $parentType,
        $source,
        $fieldNodes,
        $path)
    {
        /** @var FieldNode $fieldNode */
        $fieldNode = $fieldNodes[0];

        $field = $parentType->getFields()[$fieldNode->getName()->getValue()];

        $inputValues = $fieldNode->getArguments() ?? [];

        $args = [];

        foreach ($inputValues as $value) {
            if ($value instanceof ArgumentNode) {
                $args[] = $value->getValue()->getValue();
            } elseif ($value instanceof StringValueNode) {
                $args[] = $value->getDefaultValue()->getValue();
            }
        }
        return $field->resolve(...$args);
    }
}
