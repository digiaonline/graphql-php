<?php

namespace Digia\GraphQL\Language\AST\Node;

trait SelectionSetTrait
{

    /**
     * @var SelectionSetNode|null
     */
    protected $selectionSet;

    /**
     * @return SelectionSetNode|null
     */
    public function getSelectionSet(): ?SelectionSetNode
    {
        return $this->selectionSet;
    }

    /**
     * @return array|null
     */
    public function getSelectionSetAsArray(): ?array
    {
        return null !== $this->selectionSet ? $this->selectionSet->toArray() : null;
    }
}
