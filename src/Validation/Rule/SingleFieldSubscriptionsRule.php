<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use function Digia\GraphQL\Validation\singleFieldOnlyMessage;

/**
 * Subscriptions must only include one field.
 *
 * A GraphQL subscription is valid only if it contains a single root field.
 */
class SingleFieldSubscriptionsRule extends AbstractRule
{
    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof OperationDefinitionNode && $node->getOperation() === 'subscription') {
            $selectionSet = $node->getSelectionSet();
            if (null !== $selectionSet) {
                $selections = $selectionSet->getSelections();
                if (\count($selections) !== 1) {
                    $this->validationContext->reportError(
                        new ValidationException(
                            singleFieldOnlyMessage((string)$node),
                            \array_slice($selections, 1)
                        )
                    );
                }
            }
        }

        return $node;
    }
}
