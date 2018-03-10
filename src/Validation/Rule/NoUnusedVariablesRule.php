<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\AST\Node\VariableNode;

/**
 * No unused variables
 *
 * A GraphQL operation is only valid if all variables defined by an operation
 * are used, either directly or within a spread fragment.
 */
class NoUnusedVariablesRule extends AbstractRule
{
    /**
     * @var array|VariableDefinitionNode[]
     */
    protected $variableDefinitions;

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            $this->variableDefinitions = [];
        }

        if ($node instanceof VariableDefinitionNode) {
            $this->variableDefinitions[] = $node;
        }

        return $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            $variableNamesUsed = [];
            $usages            = $this->validationContext->getRecursiveVariableUsages($node);
            $operationNameNode = $node->getName();
            $operationName     = null !== $operationNameNode ? $operationNameNode->getValue() : null;

            /** @var VariableNode $variableNode */
            foreach ($usages as ['node' => $variableNode]) {
                $variableNamesUsed[$variableNode->getNameValue()] = true;
            }

            foreach ($this->variableDefinitions as $variableDefinition) {
                $variableName = $variableDefinition->getVariable()->getNameValue();

                if (!isset($variableNamesUsed[$variableName])) {
                    $this->validationContext->reportError(
                        new ValidationException(
                            unusedVariableMessage($variableName, $operationName),
                            [$variableDefinition]
                        )
                    );
                }
            }
        }

        return $node;
    }
}
