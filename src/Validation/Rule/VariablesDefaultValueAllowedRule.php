<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\FragmentDefinitionNode;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Validation\ValidationException;
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
    protected function enterSelectionSet(SelectionSetNode $node): VisitorResult
    {
        return new VisitorResult(null);
    }

    /**
     * @inheritdoc
     */
    protected function enterFragmentDefinition(FragmentDefinitionNode $node): VisitorResult
    {
        return new VisitorResult(null);
    }

    /**
     * @inheritdoc
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node): VisitorResult
    {
        $variable     = $node->getVariable();
        $variableName = $variable->getNameValue();
        $defaultValue = $node->getDefaultValue();
        $type         = $this->context->getInputType();

        if (null !== $defaultValue && $type instanceof NonNullType) {
            $this->context->reportError(
                new ValidationException(
                    variableDefaultValueNotAllowedMessage($variableName, $type, (string)$type->getOfType()),
                    [$defaultValue]
                )
            );
        }

        return new VisitorResult(null); // do not traverse further.
    }
}
