<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\SerializationInterface;

trait DefaultValueTrait
{

    /**
     * @var ValueNodeInterface|SerializationInterface|null
     */
    protected $defaultValue;

    /**
     * @return ValueNodeInterface|SerializationInterface|null
     */
    public function getDefaultValue(): ?ValueNodeInterface
    {
        return $this->defaultValue;
    }

    /**
     * @return array
     */
    public function getDefaultValueAsArray(): ?array
    {
        return null !== $this->defaultValue ? $this->defaultValue->toArray() : null;
    }
}
