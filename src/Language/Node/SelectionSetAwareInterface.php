<?php

namespace Digia\GraphQL\Language\Node;

interface SelectionSetAwareInterface
{
    /**
     * @return bool
     */
    public function hasSelectionSet(): bool;

    /**
     * @return SelectionSetNode|null
     */
    public function getSelectionSet(): ?SelectionSetNode;
}
