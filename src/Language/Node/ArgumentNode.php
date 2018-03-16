<?php

namespace Digia\GraphQL\Language\Node;

class ArgumentNode extends AbstractNode implements NodeInterface, NameAwareInterface
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
