<?php

namespace Digia\GraphQL\Language\Node;

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
     * @return array|null
     */
    public function getTypeConditionAsArray(): ?array
    {
        return null !== $this->typeCondition ? $this->typeCondition->toArray() : null;
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
