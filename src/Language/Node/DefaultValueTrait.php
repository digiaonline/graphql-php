<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Util\SerializationInterface;

trait DefaultValueTrait
{
    /**
     * @var ValueNodeInterface|SerializationInterface|null
     */
    protected $defaultValue;

    /**
     * @return bool
     */
    public function hasDefaultValue(): bool
    {
        return null !== $this->defaultValue;
    }

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
