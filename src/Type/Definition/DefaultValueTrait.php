<?php

namespace Digia\GraphQL\Type\Definition;

trait DefaultValueTrait
{
    /**
     * @var mixed
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
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
