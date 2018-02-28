<?php

namespace Digia\GraphQL\Language\AST\Node;

trait TypeConditionTrait
{

    /**
     * @var NamedTypeNode
     */
    protected $typeCondition;

    /**
     * @return NamedTypeNode
     */
    public function getTypeCondition(): NamedTypeNode
    {
        return $this->typeCondition;
    }
}
