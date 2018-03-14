<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use function Digia\GraphQL\Validation\duplicateVariableMessage;

/**
 * Unique variable names
 *
 * A GraphQL operation is only valid if all its variables are uniquely named.
 */
class UniqueVariableNamesRule extends AbstractRule
{
    /**
     * @var string[]
     */
    protected $knownVariableNames = [];

    /**
     * @inheritdoc
     */
    protected function enterOperationDefinition(OperationDefinitionNode $node): ?NodeInterface
    {
        $this->knownVariableNames = [];

        return $node;
    }

    /**
     * @inheritdoc
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node): ?NodeInterface
    {
        $variable     = $node->getVariable();
        $variableName = $variable->getNameValue();

        if (isset($this->knownVariableNames[$variableName])) {
            $this->context->reportError(
                new ValidationException(
                    duplicateVariableMessage($variableName),
                    [$this->knownVariableNames[$variableName], $variable->getName()]
                )
            );
        } else {
            $this->knownVariableNames[$variableName] = $variable->getName();
        }

        return $node;
    }
}
