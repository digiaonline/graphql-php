<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
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
    protected function enterOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        $this->definedVariableNames = [];

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node): VisitorResult
    {
        $this->definedVariableNames[$node->getVariable()->getNameValue()] = true;

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function leaveOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
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

        return new VisitorResult($node);
    }
}
