<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Contract\SerializationInterface;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;

trait InterfacesTrait
{

    /**
     * @var NamedTypeNode[]
     */
    protected $interfaces;

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
}
