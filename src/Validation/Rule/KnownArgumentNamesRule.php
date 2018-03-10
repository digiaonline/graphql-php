<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\ArgumentNode;
use Digia\GraphQL\Language\AST\Node\DirectiveNode;
use Digia\GraphQL\Language\AST\Node\FieldNode;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\Argument;
use function Digia\GraphQL\Util\suggestionList;

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
            $argumentDefinition = $this->context->getArgument();

            if (null === $argumentDefinition) {
                $argumentOf = $node->getAncestor();
                if ($argumentOf instanceof FieldNode) {
                    $fieldDefinition = $this->context->getFieldDefinition();
                    $parentType      = $this->context->getParentType();

                    if (null !== $fieldDefinition && null !== $parentType) {
                        $this->context->reportError(
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
                    $directive = $this->context->getDirective();

                    if (null !== $directive) {
                        $this->context->reportError(
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
