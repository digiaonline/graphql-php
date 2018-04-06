<?php

namespace Digia\GraphQL\Language\Node;

class IntValueNode extends AbstractNode implements ValueNodeInterface
{

    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::INT;

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
