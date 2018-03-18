<?php

namespace Digia\GraphQL\Language\Node;

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

    /**
     * @param mixed|null $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
