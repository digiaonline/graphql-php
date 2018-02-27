<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Language\AST\Node\ArgumentNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\AST\Node\InputValueDefinitionNode;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Type\Definition\ObjectType;

/**
 * Class AbstractStrategy
 * @package Digia\GraphQL\Execution\Strategies
 */
abstract class ExecutionStrategy
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

    /**
     * @return array|null
     */
    abstract function execute(): ?array;

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
            /** @var FieldNode $selection */
            switch ($selection->getKind()) {
                case NodeKindEnum::FIELD:
                    $fields[$selection->getNameValue()][] = $selection;
                    break;
                case NodeKindEnum::INLINE_FRAGMENT:
                    //TODO check if should include this node
                    $this->collectFields(
                        $runtimeType,
                        $selection->getSelectionSet(),
                        $fields,
                        $visitedFragmentNames
                    );
                    break;
                case NodeKindEnum::FRAGMENT_SPREAD:
                    //TODO check if should include this node
                    $visitedFragmentNames[$selection->getNameValue()] = true;
                    /** @var FragmentDefinitionNode $fragment */
                    $fragment = $this->context->getFragments()[$selection->getNameValue()];
                    $this->collectFields(
                        $runtimeType,
                        $fragment->getSelectionSet(),
                        $fields,
                        $visitedFragmentNames
                    );
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
        foreach ($fields as $fieldName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $fieldName;

            if (!$this->isDefinedField($parentType, $fieldName)) {
                continue;
            }

            $result = $this->resolveField($parentType,
                $source,
                $fieldNodes,
                $fieldPath
            );

            $finalResults[$fieldName] = $result;
        }

        return $finalResults;
    }

    /**
     * @param ObjectType $parentType
     * @param string $fieldName
     * @return bool
     * @throws \Exception
     */
    protected function isDefinedField(ObjectType $parentType, string $fieldName)
    {
       return isset($parentType->getFields()[$fieldName]);
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

        $field = $parentType->getFields()[$fieldNode->getNameValue()];

        $inputValues = $fieldNode->getArguments() ?? [];

        $args = [];

        foreach ($inputValues as $value) {
            if ($value instanceof ArgumentNode) {
                $args[] = $value->getValue()->getValue();
            } elseif ($value instanceof InputValueDefinitionNode) {
                $args[] = $value->getDefaultValue()->getValue();
            }
        }
        return $field->resolve(...$args);
    }
}
