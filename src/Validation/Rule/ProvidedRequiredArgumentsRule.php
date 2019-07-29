<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Type\Definition\NonNullType;
use Digia\GraphQL\Validation\ValidationException;
use function Digia\GraphQL\Util\keyMap;
use function Digia\GraphQL\Validation\missingDirectiveArgumentMessage;
use function Digia\GraphQL\Validation\missingFieldArgumentMessage;

/**
 * Provided required arguments
 *
 * A field or directive is only valid if all required (non-null) field arguments
 * have been provided.
 */
class ProvidedRequiredArgumentsRule extends AbstractRule
{
    // Validate on leave to allow for deeper errors to appear first.

    /**
     * @inheritdoc
     */
    protected function leaveField(FieldNode $node): VisitorResult
    {
        $fieldDefinition = $this->context->getFieldDefinition();

        if (null === $fieldDefinition) {
            return new VisitorResult(null);
        }

        $argumentNodes   = $node->getArguments();
        $argumentNodeMap = keyMap($argumentNodes, function (ArgumentNode $argument) {
            return $argument->getNameValue();
        });

        foreach ($fieldDefinition->getArguments() as $argumentDefinition) {
            $argumentNode = $argumentNodeMap[$argumentDefinition->getName()] ?? null;
            $argumentType = $argumentDefinition->getType();
            $defaultValue = $argumentDefinition->getDefaultValue();

            if (
                null === $argumentNode
                && $argumentType instanceof NonNullType
                && null === $defaultValue
            ) {
                $this->context->reportError(
                    new ValidationException(
                        missingFieldArgumentMessage(
                            (string)$node,
                            (string)$argumentDefinition,
                            (string)$argumentType
                        ),
                        [$node]
                    )
                );
            }
        }

        return new VisitorResult($node);
    }

    /**
     * @inheritdoc
     */
    protected function leaveDirective(DirectiveNode $node): VisitorResult
    {
        $directiveDefinition = $this->context->getDirective();

        if (null === $directiveDefinition) {
            return new VisitorResult(null);
        }

        $argumentNodes   = $node->getArguments();
        $argumentNodeMap = keyMap($argumentNodes, function (ArgumentNode $argument) {
            return $argument->getNameValue();
        });

        foreach ($directiveDefinition->getArguments() as $argumentDefinition) {
            $argumentNode = $argumentNodeMap[$argumentDefinition->getName()] ?? null;
            $argumentType = $argumentDefinition->getType();

            if (null === $argumentNode && $argumentType instanceof NonNullType) {
                $this->context->reportError(
                    new ValidationException(
                        missingDirectiveArgumentMessage(
                            (string)$node,
                            (string)$argumentDefinition,
                            (string)$argumentType
                        ),
                        [$node]
                    )
                );
            }
        }

        return new VisitorResult($node);
    }
}
