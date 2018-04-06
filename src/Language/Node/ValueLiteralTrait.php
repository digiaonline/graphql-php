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
     * @return ValueNodeInterface|null
     */
    public function getValue(): ?ValueNodeInterface
    {
        return $this->value;
    }

    /**
     * @param ValueNodeInterface|null $value
     *
     * @return $this
     */
    public function setValue(?ValueNodeInterface $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getValueAsArray(): array
    {
        return null !== $this->value ? $this->value->toArray() : null;
    }
}
