<?php

namespace Digia\GraphQL\Language\AST\Node;

trait TypeConditionTrait
{

    /**
     * @var NamedTypeNode|null
     */
    protected $typeCondition;

    /**
     * @return NamedTypeNode|null
     */
    public function getTypeCondition(): ?NamedTypeNode
    {
        return $this->typeCondition;
    }

    /**
     * @param NamedTypeNode|null $typeCondition
     * @return $this
     */
    public function setTypeCondition(?NamedTypeNode $typeCondition)
    {
        $this->typeCondition = $typeCondition;
        return $this;
    }
}
