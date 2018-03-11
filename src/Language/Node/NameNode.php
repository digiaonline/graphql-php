<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class NameNode extends AbstractNode implements NodeInterface
{

    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::NAME;

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
