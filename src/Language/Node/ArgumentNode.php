<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

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
