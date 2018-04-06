<?php

namespace Digia\GraphQL\Language\Node;

trait SelectionSetTrait
{

    /**
     * @var SelectionSetNode|null
     */
    protected $selectionSet;

    /**
     * @return bool
     */
    public function hasSelectionSet(): bool
    {
        return null !== $this->selectionSet;
    }

    /**
     * @return SelectionSetNode|null
     */
    public function getSelectionSet(): ?SelectionSetNode
    {
        return $this->selectionSet;
    }

    /**
     * @param SelectionSetNode|null $selectionSet
     *
     * @return $this
     */
    public function setSelectionSet(?SelectionSetNode $selectionSet)
    {
        $this->selectionSet = $selectionSet;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getSelectionSetAsArray(): ?array
    {
        return null !== $this->selectionSet ? $this->selectionSet->toArray() : null;
    }
}
