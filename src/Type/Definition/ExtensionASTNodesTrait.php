<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\ASTNodeInterface;

trait ExtensionASTNodesTrait
{

    /**
     * @var ASTNodeInterface[]
     */
    private $extensionAstNodes = [];

    /**
     * @param ASTNodeInterface $astNode
     */
    protected function addExtensionASTNode(ASTNodeInterface $astNode): void
    {
        $this->extensionAstNodes[] = $astNode;
    }

    /**
     * @param ASTNodeInterface[] $extensionAstNodes
     */
    protected function setExtensionAstNodes(array $extensionAstNodes): void
    {
        array_map(function ($astNode) {
            $this->addExtensionASTNode($astNode);
        }, $extensionAstNodes);
    }
}
