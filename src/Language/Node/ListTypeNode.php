<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class ListTypeNode extends AbstractNode implements TypeNodeInterface
{

    use TypeTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::LIST_TYPE;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'type' => $this->getTypeAsArray(),
            'loc'  => $this->getLocationAsArray(),
        ];
    }
}
