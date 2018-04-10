<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class ArgumentNode extends AbstractNode implements NodeInterface, NameAwareInterface
{
    use NameTrait;
    use ValueLiteralTrait;

    /**
     * ArgumentNode constructor.
     *
     * @param NameNode           $name
     * @param ValueNodeInterface $value
     * @param Location|null      $location
     */
    public function __construct(NameNode $name, ValueNodeInterface $value, ?Location $location)
    {
        parent::__construct(NodeKindEnum::ARGUMENT, $location);

        $this->name  = $name;
        $this->value = $value;
    }

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
