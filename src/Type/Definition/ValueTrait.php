<?php

namespace Digia\GraphQL\Type\Definition;

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
     */
    protected function setValue($value): void
    {
        // TODO: Ensure that value is not null

        $this->value = $value;
    }
}
