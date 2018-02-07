<?php

namespace Digia\GraphQL\Type\Definition;

trait DefaultValueTrait
{

    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param mixed $defaultValue
     */
    protected function setDefaultValue($defaultValue): void
    {
        $this->defaultValue = $defaultValue;
    }
}
