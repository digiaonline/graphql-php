<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class EnumValueNode extends AbstractNode implements ValueNodeInterface
{
    use ValueTrait;

    /**
     * EnumValueNode constructor.
     *
     * @param mixed         $value
     * @param Location|null $location
     */
    public function __construct($value, ?Location $location)
    {
        parent::__construct(NodeKindEnum::ENUM, $location);

        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'  => $this->kind,
            'value' => $this->value,
        ];
    }
}
