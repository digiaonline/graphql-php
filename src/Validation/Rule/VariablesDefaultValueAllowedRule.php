<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Type\Definition\NonNullType;
use function Digia\GraphQL\Validation\variableDefaultValueNotAllowedMessage;

/**
 * Variable's default value is allowed
 *
 * A GraphQL document is only valid if all variable default values are allowed
 * due to a variable not being required.
 */
class VariablesDefaultValueAllowedRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof SelectionSetNode || $node instanceof FragmentDefinitionNode) {
            return null;
        }

        if ($node instanceof VariableDefinitionNode) {
            $variable     = $node->getVariable();
            $variableName = $variable->getNameValue();
            $defaultValue = $node->getDefaultValue();
            $type         = $this->validationContext->getInputType();

            if (null !== $defaultValue && $type instanceof NonNullType) {
                $this->validationContext->reportError(
                    new ValidationException(
                        variableDefaultValueNotAllowedMessage($variableName, $type, $type->getOfType()),
                        [$defaultValue]
                    )
                );
            }

            return null; // do not traverse further.
        }

        return $node;
    }
}
