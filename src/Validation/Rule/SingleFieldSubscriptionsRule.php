<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\OperationDefinitionNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Validation\ValidationException;
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
    protected function enterOperationDefinition(OperationDefinitionNode $node): VisitorResult
    {
        if ($node->getOperation() !== 'subscription') {
            return new VisitorResult($node);
        }

        $selectionSet = $node->getSelectionSet();

        if (null !== $selectionSet) {
            $selections = $selectionSet->getSelections();
            if (\count($selections) !== 1) {
                $this->context->reportError(
                    new ValidationException(singleFieldOnlyMessage((string)$node), \array_slice($selections, 1))
                );
            }
        }

        return new VisitorResult($node);
    }
}
