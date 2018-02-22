<?php

namespace Digia\GraphQL\Type\Definition\Behavior;

use Digia\GraphQL\Language\AST\Node\ObjectTypeExtensionNode;

trait ExtensionASTNodesTrait
{

    /**
     * @var ObjectTypeExtensionNode[]
     */
    private $extensionAstNodes = [];

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
