<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class ObjectFieldNode extends AbstractNode implements NameAwareInterface
{
    use NameTrait;
    use ValueLiteralTrait;

    /**
     * ObjectFieldNode constructor.
     *
     * @param NameNode                $name
     * @param ValueNodeInterface|null $value
     * @param Location|null           $location
     */
    public function __construct(NameNode $name, ?ValueNodeInterface $value, ?Location $location)
    {
        parent::__construct(NodeKindEnum::OBJECT_FIELD, $location);

        $this->name  = $name;
        $this->value = $value;
    }
}
