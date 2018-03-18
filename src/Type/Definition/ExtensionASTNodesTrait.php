<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;

trait ExtensionASTNodesTrait
{
    /**
     * @var ObjectTypeExtensionNode[]
     */
    protected $extensionAstNodes;

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
     * @param ObjectTypeExtensionNode[] $extensionAstNodes
     * @return $this
     */
    protected function setExtensionAstNodes(array $extensionAstNodes)
    {
        $this->extensionAstNodes = $extensionAstNodes;
        return $this;
    }
}
