<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\SerializationInterface;
use Digia\GraphQL\Language\AST\Node\NamedTypeNode;

trait TypesTrait
{

    /**
     * @var NamedTypeNode[]
     */
    protected $types;

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
    public function getTypesAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->types);
    }
}
