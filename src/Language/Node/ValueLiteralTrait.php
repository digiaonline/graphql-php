<?php

namespace Digia\GraphQL\Language\Node;

trait ValueLiteralTrait
{
    /**
     * @var ValueNodeInterface|null
     */
    protected $value;

    /**
     * @return ValueNodeInterface|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getValueAST(): array
    {
        return null !== $this->value ? $this->value->toAST() : null;
    }

    /**
     * @param ValueNodeInterface|null $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
}
