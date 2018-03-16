<?php

namespace Digia\GraphQL\Language\Node;

trait NodeTrait
{
    /**
     * @var ?NodeInterface
     */
    private $astNode;

    /**
     * @return bool
     */
    public function hasAstNode(): bool
    {
        return null !== $this->astNode;
    }

    /**
     * @return NodeInterface|null
     */
    public function getAstNode(): ?NodeInterface
    {
        return $this->astNode;
    }

    /**
     * @param NodeInterface|null $astNode
     * @return $this
     */
    protected function setAstNode(?NodeInterface $astNode)
    {
        $this->astNode = $astNode;

        return $this;
    }
}
