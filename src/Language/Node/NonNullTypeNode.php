<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class NonNullTypeNode extends AbstractNode implements TypeNodeInterface
{
    use TypeTrait;

    /**
     * NonNullTypeNode constructor.
     *
     * @param TypeNodeInterface $type
     * @param Location|null     $location
     */
    public function __construct(TypeNodeInterface $type, ?Location $location)
    {
        parent::__construct(NodeKindEnum::NON_NULL_TYPE, $location);

        $this->type = $type;
    }

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

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return (string)$this->type . '!';
    }
}
