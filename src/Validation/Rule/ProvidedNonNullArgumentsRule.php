<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\NonNullType;
use function Digia\GraphQL\Util\keyMap;
use function Digia\GraphQL\Validation\missingDirectiveArgumentMessage;
use function Digia\GraphQL\Validation\missingFieldArgumentMessage;

/**
 * Provided required arguments
 *
 * A field or directive is only valid if all required (non-null) field arguments
 * have been provided.
 */
class ProvidedNonNullArgumentsRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function leaveNode(NodeInterface $node): ?NodeInterface
    {
        // Validate on leave to allow for deeper errors to appear first.
        if ($node instanceof FieldNode) {
            $fieldDefinition = $this->validationContext->getFieldDefinition();

            if (null === $fieldDefinition) {
                return null;
            }

            $argumentNodes   = $node->getArguments();
            $argumentNodeMap = keyMap($argumentNodes, function (ArgumentNode $argument) {
                return $argument->getNameValue();
            });

            foreach ($fieldDefinition->getArguments() as $argumentDefinition) {
                $argumentNode = $argumentNodeMap[$argumentDefinition->getName()] ?? null;
                $argumentType = $argumentDefinition->getType();

                if (null === $argumentNode && $argumentType instanceof NonNullType) {
                    $this->validationContext->reportError(
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
        }

        if ($node instanceof DirectiveNode) {
            $directiveDefinition = $this->validationContext->getDirective();

            if (null === $directiveDefinition) {
                return null;
            }

            $argumentNodes   = $node->getArguments();
            $argumentNodeMap = keyMap($argumentNodes, function (ArgumentNode $argument) {
                return $argument->getNameValue();
            });

            foreach ($directiveDefinition->getArguments() as $argumentDefinition) {
                $argumentNode = $argumentNodeMap[$argumentDefinition->getName()] ?? null;
                $argumentType = $argumentDefinition->getType();

                if (null === $argumentNode && $argumentType instanceof NonNullType) {
                    $this->validationContext->reportError(
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
        }

        return $node;
    }
}
