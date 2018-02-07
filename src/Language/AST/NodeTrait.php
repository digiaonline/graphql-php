<?php

namespace Digia\GraphQL\Language\AST;

trait ASTNodeTrait
{

    /**
     * @var ?ASTNodeInterface
     */
    protected $astNode;

    /**
     * @return ASTNodeInterface|null
     */
    public function getAstNode(): ?ASTNodeInterface
    {
        return $this->astNode;
    }

    /**
     * @param ASTNodeInterface|null $astNode
     */
    protected function setAstNode(?ASTNodeInterface $astNode): void
    {
        $this->astNode = $astNode;
    }
}
