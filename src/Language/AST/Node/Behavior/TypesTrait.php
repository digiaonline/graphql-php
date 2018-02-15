<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

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
}
