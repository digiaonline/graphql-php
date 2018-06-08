<?php

namespace Digia\GraphQL\Language\Node;

trait DefaultValueTrait
{
    /**
     * @var ValueNodeInterface|null
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
     * @return ValueNodeInterface|null
     */
    public function getDefaultValue(): ?ValueNodeInterface
    {
        return $this->defaultValue;
    }

    /**
     * @return array
     */
    public function getDefaultValueAST(): ?array
    {
        return null !== $this->defaultValue ? $this->defaultValue->toAST() : null;
    }
}
