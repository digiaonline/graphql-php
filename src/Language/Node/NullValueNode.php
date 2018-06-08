<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class NullValueNode extends AbstractNode implements ValueNodeInterface
{
    /**
     * NullValueNode constructor.
     *
     * @param Location|null $location
     */
    public function __construct(?Location $location)
    {
        parent::__construct(NodeKindEnum::NULL, $location);
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind' => $this->kind,
            'loc'  => $this->getLocationAST(),
        ];
    }
}
