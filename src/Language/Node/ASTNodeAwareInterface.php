<?php

namespace Digia\GraphQL\Language\Node;

interface ASTNodeAwareInterface
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
