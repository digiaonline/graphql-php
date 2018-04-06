<?php

namespace Digia\GraphQL\Language\Node;

class NonNullTypeNode extends AbstractNode implements TypeNodeInterface
{

    use TypeTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::NON_NULL_TYPE;

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'type' => $this->getTypeAsArray(),
            'loc' => $this->getLocationAsArray(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return (string)$this->getType().'!';
    }
}
