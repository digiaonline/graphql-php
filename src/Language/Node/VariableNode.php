<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class VariableNode extends AbstractNode implements ValueNodeInterface, NameAwareInterface
{
    use NameTrait;

    /**
     * VariableNode constructor.
     *
     * @param NameNode      $name
     * @param Location|null $location
     */
    public function __construct(NameNode $name, ?Location $location)
    {
        parent::__construct(NodeKindEnum::VARIABLE, $location);

        $this->name = $name;
    }
}
