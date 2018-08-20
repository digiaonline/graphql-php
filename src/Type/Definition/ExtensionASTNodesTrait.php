<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Language\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;

trait ExtensionASTNodesTrait
{
    /**
     * @var ObjectTypeExtensionNode[]|InterfaceTypeExtensionNode[]
     */
    protected $extensionAstNodes = [];

    /**
     * @return bool
     */
    public function hasExtensionAstNodes(): bool
    {
        return !empty($this->extensionAstNodes);
    }

    /**
     * @return ObjectTypeExtensionNode[]|InterfaceTypeExtensionNode[]
     */
    public function getExtensionAstNodes(): array
    {
        return $this->extensionAstNodes;
    }
}
