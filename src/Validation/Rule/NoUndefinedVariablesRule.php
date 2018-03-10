<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\AST\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\AST\Node\VariableNode;

/**
 * No undefined variables
 *
 * A GraphQL operation is only valid if all variables encountered, both directly
 * and via fragment spreads, are defined by that operation.
 */
class NoUndefinedVariablesRule extends AbstractRule
{
    /**
     * @var array
     */
    protected $definedVariableNames;

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            $this->definedVariableNames = [];
        }

        if ($node instanceof VariableDefinitionNode) {
            $this->definedVariableNames[$node->getVariable()->getNameValue()] = true;
        }

        return $node;
    }

    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            $usages = $this->context->getRecursiveVariableUsages($node);

            foreach ($usages as ['node' => $variableNode]) {
                /** @var VariableNode $variableNode */
                $variableName = $variableNode->getNameValue();

                if (!isset($this->definedVariableNames[$variableName])) {
                    $operationName = $node->getName();
                    $this->context->reportError(
                        new ValidationException(
                            undefinedVariableMessage($variableName, $operationName ? $operationName->getValue() : null),
                            [$variableNode, $node]
                        )
                    );
                }
            }
        }

        return $node;
    }
}
