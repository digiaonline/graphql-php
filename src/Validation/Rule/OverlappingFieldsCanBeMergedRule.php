<?php

namespace Digia\GraphQL\Validation\Rule;

use Digia\GraphQL\Language\Node\SelectionSetNode;
use Digia\GraphQL\Language\Visitor\VisitorResult;
use Digia\GraphQL\Validation\Conflict\ConflictFinder;
use Digia\GraphQL\Validation\ValidationException;
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
    /**
     * @var ConflictFinder
     */
    protected $conflictFinder;

    /**
     * OverlappingFieldsCanBeMergedRule constructor.
     */
    public function __construct()
    {
        $this->conflictFinder = new ConflictFinder();
    }

    /**
     * @inheritdoc
     */
    protected function enterSelectionSet(SelectionSetNode $node): VisitorResult
    {
        $this->conflictFinder->setContext($this->context);

        $parentType = $this->context->getParentType();
        $conflicts  = $this->conflictFinder->findConflictsWithinSelectionSet($node, $parentType);

        foreach ($conflicts as $conflict) {
            $this->context->reportError(
                new ValidationException(
                    fieldsConflictMessage($conflict->getResponseName(), $conflict->getReason()),
                    $conflict->getAllFields()
                )
            );
        }

        return new VisitorResult($node);
    }
}
