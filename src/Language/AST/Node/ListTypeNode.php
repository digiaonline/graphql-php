<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\TypeTrait;
use Digia\GraphQL\Language\AST\Node\TypeNodeInterface;

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
