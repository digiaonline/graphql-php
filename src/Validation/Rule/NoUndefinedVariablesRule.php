<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use function Digia\GraphQL\Validation\undefinedVariableMessage;

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
            $usages = $this->validationContext->getRecursiveVariableUsages($node);

            foreach ($usages as ['node' => $variableNode]) {
                /** @var VariableNode $variableNode */
                $variableName = $variableNode->getNameValue();

                if (!isset($this->definedVariableNames[$variableName])) {
                    $operationName = $node->getName();
                    $this->validationContext->reportError(
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
