<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class BooleanValueNode extends AbstractNode implements ValueNodeInterface, ValueAwareInterface
{
    use ValueTrait;

    /**
     * BooleanValueNode constructor.
     *
     * @param mixed         $value
     * @param Location|null $location
     */
    public function __construct($value, ?Location $location)
    {
        parent::__construct(NodeKindEnum::BOOLEAN, $location);

        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'  => $this->kind,
            'value' => $this->value,
            'loc'   => $this->getLocationAsArray(),
        ];
    }
}
