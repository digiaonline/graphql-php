<?php

namespace Digia\GraphQL\Language\Node;

trait InterfacesTrait
{
    /**
     * @var NamedTypeNode[]
     */
    protected $interfaces;

    /**
     * @return bool
     */
    public function hasInterfaces(): bool
    {
        return !empty($this->interfaces);
    }

    /**
     * @return NamedTypeNode[]
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * @return array
     */
    public function getInterfacesAST(): array
    {
        return \array_map(function (NamedTypeNode $node) {
            return $node->toAST();
        }, $this->interfaces);
    }

    /**
     * @param array|NamedTypeNode[] $interfaces
     * @return $this
     */
    public function setInterfaces(array $interfaces)
    {
        $this->interfaces = $interfaces;
        return $this;
    }
}
