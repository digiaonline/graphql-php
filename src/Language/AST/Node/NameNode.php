<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\Behavior\ValueLiteralTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class NameNode extends AbstractNode implements NodeInterface
{

    use ValueLiteralTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::NAME;

    public function toArray(): array
    {
        return [
            'kind'  => $this->kind,
            'loc'   => $this->getLocationAsArray(),
            'value' => $this->value,
        ];
    }
}
