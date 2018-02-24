<?php

namespace Digia\GraphQL\Type\Definition\Behavior;

trait ValueTrait
{

    /**
     * @var mixed|null
     */
    protected $value;

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
