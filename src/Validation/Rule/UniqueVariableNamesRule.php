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
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode) {
            $this->knownVariableNames = [];
        }

        if ($node instanceof VariableDefinitionNode) {
            $variable     = $node->getVariable();
            $variableName = $variable->getNameValue();

            if (isset($this->knownVariableNames[$variableName])) {
                $this->validationContext->reportError(
                    new ValidationException(
                        duplicateVariableMessage($variableName),
                        [$this->knownVariableNames[$variableName], $variable->getName()]
                    )
                );
            } else {
                $this->knownVariableNames[$variableName] = $variable->getName();
            }
        }

        return $node;
    }
}
