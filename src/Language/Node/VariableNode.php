<?php

namespace Digia\GraphQL\Language\Node;

class VariableNode extends AbstractNode implements ValueNodeInterface, NameAwareInterface
{
    use NameTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::VARIABLE;
}
