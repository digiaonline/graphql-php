<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class BooleanValueNode extends AbstractNode implements ValueNodeInterface
{

    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::BOOLEAN;

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
