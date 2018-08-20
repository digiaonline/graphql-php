<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\ArgumentNode;
use Digia\GraphQL\Language\Node\DirectiveNode;
use Digia\GraphQL\Language\Node\FieldNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Type\Definition\Argument;
use function Digia\GraphQL\printNode;
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
    protected function enterArgument(ArgumentNode $node): ?NodeInterface
    {
        $argumentDefinition = $this->context->getArgument();

        if (null === $argumentDefinition) {
            $argumentOf = $node->getAncestor();

            if ($argumentOf instanceof FieldNode) {
                return $this->validateField($node);
            }

            if ($argumentOf instanceof DirectiveNode) {
                return $this->validateDirective($node);
            }
        }

        return $node;
    }

    /**
     * @param NodeInterface $node
     * @return NodeInterface|null
     */
    protected function validateField(NodeInterface $node): ?NodeInterface
    {
        $fieldDefinition = $this->context->getFieldDefinition();
        $parentType      = $this->context->getParentType();

        if (null !== $fieldDefinition && null !== $parentType) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $options = \array_map(function (Argument $argument) {
                return $argument->getName();
            }, $fieldDefinition->getArguments());

            $suggestions = suggestionList(printNode($node), $options);

            $this->context->reportError(
                new ValidationException(
                    unknownArgumentMessage(
                        (string)$node,
                        (string)$fieldDefinition,
                        (string)$parentType,
                        $suggestions
                    ),
                    [$node]
                )
            );
        }

        return $node;
    }

    /**
     * @param NodeInterface $node
     * @return NodeInterface|null
     */
    protected function validateDirective(NodeInterface $node): ?NodeInterface
    {
        $directive = $this->context->getDirective();

        if (null !== $directive) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $options = \array_map(function (Argument $argument) {
                return $argument->getName();
            }, $directive->getArguments());

            $suggestions = suggestionList((string)$node, $options);

            $this->context->reportError(
                new ValidationException(
                    unknownDirectiveArgumentMessage((string)$node, (string)$directive, $suggestions),
                    [$node]
                )
            );
        }

        return $node;
    }
}
