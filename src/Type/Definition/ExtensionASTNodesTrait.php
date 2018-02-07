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
     * @return $this
     */
    protected function setExtensionAstNodes(array $extensionAstNodes)
    {
        foreach ($extensionAstNodes as $astNode) {
            $this->addExtensionASTNode($astNode);
        }

        return $this;
    }
}
