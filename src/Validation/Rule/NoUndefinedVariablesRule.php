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
    protected function enterOperationDefinition(OperationDefinitionNode $node
    ): ?NodeInterface
    {
        $this->definedVariableNames = [];

        return $node;
    }

    /**
     * @inheritdoc
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node
    ): ?NodeInterface
    {
        $this->definedVariableNames[$node->getVariable()
            ->getNameValue()] = true;

        return $node;
    }

    /**
     * @inheritdoc
     */
    protected function leaveOperationDefinition(OperationDefinitionNode $node
    ): ?NodeInterface
    {
        $usages = $this->context->getRecursiveVariableUsages($node);

        foreach ($usages as ['node' => $variableNode]) {
            /** @var VariableNode $variableNode */
            $variableName = $variableNode->getNameValue();

            if (!isset($this->definedVariableNames[$variableName])) {
                $operationName = $node->getName();
                $this->context->reportError(
                    new ValidationException(
                        undefinedVariableMessage($variableName,
                            $operationName ? $operationName->getValue() : null),
                        [$variableNode, $node]
                    )
                );
            }
        }

        return $node;
    }
}
