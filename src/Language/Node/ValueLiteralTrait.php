<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Util\SerializationInterface;

trait ValueLiteralTrait
{

    /**
     * @var ValueNodeInterface|SerializationInterface|null
     */
    protected $value;

    /**
     * @return ValueNodeInterface|SerializationInterface|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getValueAsArray(): array
    {
        return null !== $this->value ? $this->value->toArray() : null;
    }

    /**
     * @param ValueNodeInterface|SerializationInterface|null $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
