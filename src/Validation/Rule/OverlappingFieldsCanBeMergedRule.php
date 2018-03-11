<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Error\ValidationException;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Validation\Conflict\FindsConflictsTrait;
use Digia\GraphQL\Validation\Conflict\Map;
use Digia\GraphQL\Validation\Conflict\PairSet;
use function Digia\GraphQL\Validation\fieldsConflictMessage;

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
     * OverlappingFieldsCanBeMergedRule constructor.
     */
    public function __construct()
    {
        $this->cachedFieldsAndFragmentNames = new Map();
        $this->comparedFragmentPairs        = new PairSet();
    }


    /**
     * @inheritdoc
     */
    public function enterNode(NodeInterface $node): ?NodeInterface
    {
        if ($node instanceof SelectionSetNode) {
            $parentType = $this->validationContext->getParentType();
            $conflicts  = $this->findConflictsWithinSelectionSet(
                $this->cachedFieldsAndFragmentNames,
                $this->comparedFragmentPairs,
                $node,
                $parentType
            );

            foreach ($conflicts as $conflict) {
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
