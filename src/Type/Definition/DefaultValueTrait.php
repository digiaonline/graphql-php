<?php

namespace Digia\GraphQL\Type\Definition;

trait DefaultValueTrait
{
    /**
     * @var mixed
     */
    protected $defaultValue;

    /**
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
