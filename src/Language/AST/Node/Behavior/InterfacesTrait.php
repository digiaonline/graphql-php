<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

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
}
