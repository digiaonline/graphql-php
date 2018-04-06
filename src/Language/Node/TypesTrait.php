<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Util\SerializationInterface;

trait TypesTrait
{

    /**
     * @var NamedTypeNode[]
     */
    protected $types;

    /**
     * @return bool
     */
    public function hasTypes(): bool
    {
        return !empty($this->types);
    }

    /**
     * @return NamedTypeNode[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param array|NamedTypeNode[] $types
     *
     * @return $this
     */
    public function setTypes(array $types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * @return array
     */
    public function getTypesAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->types);
    }
}
