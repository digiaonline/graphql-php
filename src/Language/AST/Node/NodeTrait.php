<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

trait NodeTrait
{

    /**
     * @var ?NodeInterface
     */
    private $astNode;

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
