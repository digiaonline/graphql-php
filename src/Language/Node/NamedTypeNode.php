<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class NamedTypeNode extends AbstractNode implements TypeNodeInterface, NameAwareInterface
{
    use NameTrait;

    /**
     * NamedTypeNode constructor.
     *
     * @param NameNode      $name
     * @param Location|null $location
     */
    public function __construct(NameNode $name, ?Location $location)
    {
        parent::__construct(NodeKindEnum::NAMED_TYPE, $location);

        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'name' => $this->getNameAsArray(),
            'loc'  => $this->getLocationAsArray(),
        ];
    }
}
