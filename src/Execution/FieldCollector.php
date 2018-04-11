<?php

namespace Digia\GraphQL\Execution;

use Digia\GraphQL\Error\InvalidTypeException;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\FragmentSpreadNode;
use Digia\GraphQL\Language\Node\InlineFragmentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Type\Definition\AbstractTypeInterface;
use Digia\GraphQL\Type\Definition\ObjectType;
use function Digia\GraphQL\Util\typeFromAST;

/**
 * Class FieldCollector
 * @package Digia\GraphQL\Execution
 */
class FieldCollector
{
    /**
     * @var ExecutionContext
     */
    protected $context;

    /**
     * FieldCollector constructor.
     * @param ExecutionContext $context
     */
    public function __construct(ExecutionContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param ObjectType       $runtimeType
     * @param SelectionSetNode $selectionSet
     * @param                  $fields
     * @param                  $visitedFragmentNames
     * @return mixed
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    public function collectFields(
        ObjectType $runtimeType,
        SelectionSetNode $selectionSet,
        &$fields,
        &$visitedFragmentNames
    ) {
        foreach ($selectionSet->getSelections() as $selection) {
            // Check if this Node should be included first
            if (!$this->shouldIncludeNode($selection)) {
                continue;
            }
            // Collect fields
            if ($selection instanceof FieldNode) {
                $fieldName = $this->getFieldNameKey($selection);

                if (!isset($fields[$fieldName])) {
                    $fields[$fieldName] = [];
                }

                $fields[$fieldName][] = $selection;
            } elseif ($selection instanceof InlineFragmentNode) {
                if (!$this->doesFragmentConditionMatch($selection, $runtimeType)) {
                    continue;
                }

                $this->collectFields($runtimeType, $selection->getSelectionSet(), $fields, $visitedFragmentNames);
            } elseif ($selection instanceof FragmentSpreadNode) {
                $fragmentName = $selection->getNameValue();

                if (!empty($visitedFragmentNames[$fragmentName])) {
                    continue;
                }

                $visitedFragmentNames[$fragmentName] = true;
                /** @var FragmentDefinitionNode $fragment */
                $fragment = $this->context->getFragments()[$fragmentName];
                $this->collectFields($runtimeType, $fragment->getSelectionSet(), $fields, $visitedFragmentNames);
            }
        }

        return $fields;
    }

    /**
     * @TODO: consider to move this to FieldNode
     * @param FieldNode $node
     * @return string
     */
    private function getFieldNameKey(FieldNode $node): string
    {
        return $node->getAlias() ? $node->getAlias()->getValue() : $node->getNameValue();
    }

    /**
     * @param $node
     * @return bool
     * @throws InvalidTypeException
     * @throws \Digia\GraphQL\Error\ExecutionException
     * @throws \Digia\GraphQL\Error\InvariantException
     */
    private function shouldIncludeNode(NodeInterface $node): bool
    {

        $contextVariables = $this->context->getVariableValues();

        $skip = coerceDirectiveValues(SkipDirective(), $node, $contextVariables);

        if ($skip && $skip['if'] === true) {
            return false;
        }

        $include = coerceDirectiveValues(IncludeDirective(), $node, $contextVariables);

        if ($include && $include['if'] === false) {
            return false;
        }

        return true;
    }

    /**
     * @param FragmentDefinitionNode|InlineFragmentNode $fragment
     * @param ObjectType                                $type
     * @return bool
     * @throws InvalidTypeException
     */
    private function doesFragmentConditionMatch(
        NodeInterface $fragment,
        ObjectType $type
    ): bool {
        $typeConditionNode = $fragment->getTypeCondition();

        if (!$typeConditionNode) {
            return true;
        }

        $conditionalType = typeFromAST($this->context->getSchema(), $typeConditionNode);

        if ($conditionalType === $type) {
            return true;
        }

        if ($conditionalType instanceof AbstractTypeInterface) {
            return $this->context->getSchema()->isPossibleType($conditionalType, $type);
        }

        return false;
    }

}