<?php

namespace Digia\GraphQL\Language\Node;

class FloatValueNode extends AbstractNode implements ValueNodeInterface
{

    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::FLOAT;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'loc' => $this->getLocationAsArray(),
            'value' => $this->value,
        ];
    }
}
