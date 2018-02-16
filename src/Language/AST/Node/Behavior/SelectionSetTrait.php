<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\SelectionSetNode;

trait SelectionSetTrait
{

    /**
     * @var SelectionSetNode
     */
    protected $selectionSet;

    /**
     * @return SelectionSetNode
     */
    public function getSelectionSet(): SelectionSetNode
    {
        return $this->selectionSet;
    }
}
