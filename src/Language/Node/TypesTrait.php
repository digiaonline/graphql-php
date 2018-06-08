<?php

namespace Digia\GraphQL\Language\Node;

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
     * @return array
     */
    public function getTypesAST(): array
    {
        return \array_map(function (NamedTypeNode $node) {
            return $node->toAST();
        }, $this->types);
    }

    /**
     * @param array|NamedTypeNode[] $types
     * @return $this
     */
    public function setTypes(array $types)
    {
        $this->types = $types;
        return $this;
    }
}
