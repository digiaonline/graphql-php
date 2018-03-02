<?php

namespace Digia\GraphQL\Language\AST\Node;

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
     * @return array
     */
    public function getValueAsArray(): array
    {
        return null !== $this->value ? $this->value->toArray() : null;
    }
}
