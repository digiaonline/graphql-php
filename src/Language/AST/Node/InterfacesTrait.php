<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Util\SerializationInterface;

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
    public function getInterfacesAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
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
