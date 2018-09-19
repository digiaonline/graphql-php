<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Node\VariableNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use function Digia\GraphQL\Validation\unusedVariableMessage;

/**
 * No unused variables
 *
 * A GraphQL operation is only valid if all variables defined by an operation
 * are used, either directly or within a spread fragment.
 */
class NoUnusedVariablesRule extends AbstractRule
{
    /**
     * @var VariableDefinitionNode[]
     */
    protected $variableDefinitions;

    /**
     * @inheritdoc
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        $this->variableDefinitions = [];

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node): VisitorResult
    {
        $this->variableDefinitions[] = $node;

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function leaveOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        $variableNamesUsed = [];
        $usages            = $this->context->getRecursiveVariableUsages($node);
        $operationNameNode = $node->getName();
        $operationName     = null !== $operationNameNode ? $operationNameNode->getValue() : null;

        /** @var VariableNode $variableNode */
        foreach ($usages as ['node' => $variableNode]) {
            $variableNamesUsed[$variableNode->getNameValue()] = true;
        }

        foreach ($this->variableDefinitions as $variableDefinition) {
            $variableName = $variableDefinition->getVariable()->getNameValue();

            if (!isset($variableNamesUsed[$variableName])) {
                $this->context->reportError(
                    new ValidationException(
                        unusedVariableMessage($variableName, $operationName),
                        [$variableDefinition]
                    )
                );
            }
        }

        return new VisitorResult($node);
    }
}
