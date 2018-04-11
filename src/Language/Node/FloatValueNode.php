<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class FloatValueNode extends AbstractNode implements ValueNodeInterface
{
    use ValueTrait;

    /**
     * FloatValueNode constructor.
     *
     * @param mixed         $value
     * @param Location|null $location
     */
    public function __construct($value, ?Location $location)
    {
        parent::__construct(NodeKindEnum::FLOAT, $location);

        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'  => $this->kind,
            'loc'   => $this->getLocationAsArray(),
            'value' => $this->value,
        ];
    }
}
