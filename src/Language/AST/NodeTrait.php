<?php

namespace Digia\GraphQL\Language\AST;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

trait NodeTrait
{

    /**
     * @var ?NodeInterface
     */
    protected $astNode;

    /**
     * @return NodeInterface|null
     */
    public function getAstNode(): ?NodeInterface
    {
        return $this->astNode;
    }

    /**
     * @param NodeInterface|null $astNode
     */
    protected function setAstNode(?NodeInterface $astNode): void
    {
        $this->astNode = $astNode;
    }
}
