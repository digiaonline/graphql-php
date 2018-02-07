<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

trait ExtensionASTNodesTrait
{

    /**
     * @var NodeInterface[]
     */
    private $extensionAstNodes = [];

    /**
     * @param NodeInterface $astNode
     */
    protected function addExtensionASTNode(NodeInterface $astNode): void
    {
        $this->extensionAstNodes[] = $astNode;
    }

    /**
     * @param NodeInterface[] $extensionAstNodes
     */
    protected function setExtensionAstNodes(array $extensionAstNodes): void
    {
        array_map(function ($astNode) {
            $this->addExtensionASTNode($astNode);
        }, $extensionAstNodes);
    }
}
