<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\AST\Node\NodeInterface;
use Digia\GraphQL\Language\AST\Node\SelectionSetNode;
use Digia\GraphQL\Validation\Conflict\FindsConflictsTrait;


/**
 * Overlapping fields can be merged
 *
 * A selection set is only valid if all fields (including spreading any
 * fragments) either correspond to distinct response names or can be merged
 * without ambiguity.
 */
class OverlappingFieldsCanBeMergedRule extends AbstractRule
{
    use FindsConflictsTrait;

    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof SelectionSetNode) {
            $parentType = $this->validationContext->getParentType();

            foreach ($this->findConflictsWithinSelectionSet($node, $parentType) as $conflict) {
                $this->validationContext->reportError(
                    new ValidationException(
                        fieldsConflictMessage($conflict->getResponseName(), $conflict->getReason()),
                        $conflict->getAllFields()
                    )
                );
            }
        }

        return $node;
    }
}
