<?php

namespace Digia\GraphQL\Language\Node;

interface NodeAwareInterface
{

    /**
     * @return bool
     */
    public function hasAstNode(): bool;

    /**
     * @return NodeInterface|null
     */
    public function getAstNode(): ?NodeInterface;
}
