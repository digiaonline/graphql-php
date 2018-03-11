<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class StringValueNode extends AbstractNode implements ValueNodeInterface
{

    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::STRING;

    /**
     * @var bool
     */
    protected $block;

    /**
     * @return bool
     */
    public function isBlock(): bool
    {
        return $this->block;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'  => $this->kind,
            'loc'   => $this->getLocationAsArray(),
            'block' => $this->block,
            'value' => $this->value,
        ];
    }
}
