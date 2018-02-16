<?php

namespace Digia\GraphQL\Behavior;

trait ValueTrait
{

    /**
     * @var mixed
     */
    private $value;

    /**
     * @return bool
     */
    public function hasValue(): bool
    {
        return $this->value !== null;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    protected function setValue($value)
    {
        // TODO: Ensure that value is not null

        $this->value = $value;

        return $this;
    }
}
