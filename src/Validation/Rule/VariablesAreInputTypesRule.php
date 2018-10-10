<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ConversionException;
use Digia\GraphQL\Error\InvariantException;
use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Util\TypeASTConverter;
use function Digia\GraphQL\printNode;
use function Digia\GraphQL\Type\isInputType;
use function Digia\GraphQL\Validation\nonInputTypeOnVariableMessage;

/**
 * Variables are input types
 *
 * A GraphQL operation is only valid if all the variables it defines are of
 * input types (scalar, enum, or input object).
 */
class VariablesAreInputTypesRule extends AbstractRule
{
    /**
     * @inheritdoc
     *
     * @throws ConversionException
     * @throws InvariantException
     */
    protected function enterVariableDefinition(VariableDefinitionNode $node): VisitorResult
    {
        $type = TypeASTConverter::convert($this->context->getSchema(), $node->getType());

        if (!isInputType($type)) {
            $variableName = $node->getVariable()->getNameValue();

            $this->context->reportError(
                new ValidationException(
                    nonInputTypeOnVariableMessage($variableName, printNode($node->getType())),
                    [$node->getType()]
                )
            );
        }

        return new VisitorResult($node);
    }
}
