<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;

trait ExtensionASTNodesTrait
{

    /**
     * @var ObjectTypeExtensionNode[]
     */
    private $extensionAstNodes;

    /**
     * @return bool
     */
    public function hasExtensionAstNodes(): bool
    {
        return !empty($this->extensionAstNodes);
    }

    /**
     * @return ObjectTypeExtensionNode[]
     */
    public function getExtensionAstNodes(): array
    {
        return $this->extensionAstNodes;
    }

    /**
     * @param ObjectTypeExtensionNode $astNode
     * @return $this
     */
    protected function addExtensionASTNode(ObjectTypeExtensionNode $astNode)
    {
        $this->extensionAstNodes[] = $astNode;

        return $this;
    }

    /**
     * @param ObjectTypeExtensionNode[] $extensionAstNodes
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
