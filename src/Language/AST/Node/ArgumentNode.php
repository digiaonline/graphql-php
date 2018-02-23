<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\ValueLiteralTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class ArgumentNode extends AbstractNode implements NodeInterface
{

    use NameTrait;
    use ValueLiteralTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::ARGUMENT;

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'kind'  => $this->kind,
            'name'  => $this->getNameAsArray(),
            'value' => $this->getValueAsArray(),
            'loc'   => $this->getLocationAsArray(),
        ];
    }
}
