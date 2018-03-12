<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\Argument;
use function Digia\GraphQL\Util\suggestionList;
use function Digia\GraphQL\Validation\unknownArgumentMessage;
use function Digia\GraphQL\Validation\unknownDirectiveArgumentMessage;

/**
 * Known argument names
 *
 * A GraphQL field is only valid if all supplied arguments are defined by
 * that field.
 */
class KnownArgumentNamesRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof ArgumentNode) {
            $argumentDefinition = $this->validationContext->getArgument();

            if (null === $argumentDefinition) {
                $argumentOf = $node->getAncestor();
                if ($argumentOf instanceof FieldNode) {
                    $fieldDefinition = $this->validationContext->getFieldDefinition();
                    $parentType      = $this->validationContext->getParentType();

                    if (null !== $fieldDefinition && null !== $parentType) {
                        $this->validationContext->reportError(
                            new ValidationException(
                                unknownArgumentMessage(
                                    (string)$node,
                                    (string)$fieldDefinition,
                                    (string)$parentType,
                                    suggestionList(
                                        $node->getNameValue(),
                                        array_map(function (Argument $argument) {
                                            return $argument->getName();
                                        }, $fieldDefinition->getArguments())
                                    )
                                ),
                                [$node]
                            )
                        );
                    }
                } elseif ($argumentOf instanceof DirectiveNode) {
                    $directive = $this->validationContext->getDirective();

                    if (null !== $directive) {
                        $this->validationContext->reportError(
                            new ValidationException(
                                unknownDirectiveArgumentMessage(
                                    (string)$node,
                                    (string)$directive,
                                    suggestionList(
                                        (string)$node,
                                        array_map(function (Argument $argument) {
                                            return $argument->getName();
                                        }, $directive->getArguments())
                                    )
                                ),
                                [$node]
                            )
                        );
                    }
                }
            }
        }

        return $node;
    }
}
