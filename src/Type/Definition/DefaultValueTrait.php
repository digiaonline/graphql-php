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
     * @return $this
     */
    protected function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }
}
