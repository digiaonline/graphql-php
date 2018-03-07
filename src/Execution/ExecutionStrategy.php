<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\GraphQLError;
use Digia\GraphQL\Execution\Resolver\ResolveInfo;
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
     * @var array
     */
    protected $finalResult;

    /**
     * AbstractStrategy constructor.
     * @param ExecutionContext        $context
     *
     * @param OperationDefinitionNode $operation
     */
    public function __construct(
        ExecutionContext $context,
        OperationDefinitionNode $operation,
        $rootValue
    ) {
        $this->context   = $context;
        $this->operation = $operation;
        $this->rootValue = $rootValue;
    }

    /**
     * @return array|null
     */
    abstract function execute(): ?array;

    /**
     * @param ObjectType       $runtimeType
     * @param SelectionSetNode $selectionSet
     * @param                  $fields
     * @param                  $visitedFragmentNames
     * @return \ArrayObject
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
                    $name = $this->getFieldNameKey($selection);
                    if (!isset($runtimeType->getFields()[$selection->getNameValue()])) {
                        continue;
                    }
                    if (!isset($fields[$name])) {
                        $fields[$name] = new \ArrayObject();
                    }
                    $fields[$name][] = $selection;
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
                    if (!empty($visitedFragmentNames[$selection->getNameValue()])) {
                        continue;
                    }
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
     * @param FieldNode $node
     * @return string
     */
    private function getFieldNameKey(FieldNode $node)
    {
        return $node->getAlias()
            ? $node->getAlias()->getValue()
            : $node->getNameValue();
    }

    /**
     * Implements the "Evaluating selection sets" section of the spec
     * for "read" mode.
     * @param ObjectType $parentType
     * @param            $source
     * @param            $path
     * @param            $fields
     *
     * @return array
     *
     * @throws GraphQLError|\Exception
     */
    protected function executeFields(
        ObjectType $parentType,
        $source,
        $path,
        $fields
    ): array {
        $finalResults = [];

        foreach ($fields as $fieldName => $fieldNodes) {
            $fieldPath   = $path;
            $fieldPath[] = $fieldName;

            $result = $this->resolveField($parentType,
                [],
                $fieldNodes,
                $fieldPath
            );

            $finalResults[$fieldName] = $result;
        }

        return $finalResults;
    }

    /**
     * @param ObjectType $parentType
     * @param            $source
     * @param            $fieldNodes
     * @param            $path
     *
     * @return mixed
     *
     * @throws GraphQLError|\Exception
     */
    protected function resolveField(
        ObjectType $parentType,
        $source,
        $fieldNodes,
        $path
    ) {
        $result = [];
        /** @var FieldNode $fieldNode */
        foreach ($fieldNodes as $fieldNode) {
            $field       = $parentType->getFields()[$fieldNode->getNameValue()];
            $inputValues = $fieldNode->getArguments() ?? [];
            $args        = [];

            $returnType = null;

//            $info = new ResolveInfo([
//                'fieldName'      => $fieldNode->getNameValue(),
//                'fieldNodes'     => $fieldNodes,
//                'returnType'     => $returnType,
//                'parentType'     => $parentType,
//                'path'           => $path,
//                'schema'         => $this->context->getSchema(),
//                'fragments'      => $this->context->getFragments(),
//                'rootValue'      => $this->context->getRootValue(),
//                'operation'      => $this->context->getOperation(),
//                'variableValues' => $this->context->getVariableValues(),
//            ]);

            foreach ($inputValues as $value) {
                if ($value instanceof ArgumentNode) {
                    $args[] = $value->getValue()->getValue();
                } elseif ($value instanceof InputValueDefinitionNode) {
                    $args[] = $value->getDefaultValue()->getValue();
                }
            }

            $subResult = $field->resolve(...$args);

            if ($fieldNode->getSelectionSet() !== null) {
                $fields = $this->collectFields(
                    $parentType,
                    $fieldNode->getSelectionSet(),
                    new \ArrayObject(),
                    new \ArrayObject()
                );

                $data = $this->executeFields(
                    $parentType,
                    $source,
                    $path,
                    $fields
                );
                //@TODO Find better way to implement this
                // For testSimpleMutation test message is resolve already in $subResult above
                $subResult = array_merge_recursive(
                    $result,
                    array_merge_recursive($data, $subResult)
                );
            }

            $result = $subResult;
        }

        return $result;
    }
}
