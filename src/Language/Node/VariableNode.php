<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class VariableNode extends AbstractNode implements ValueNodeInterface
{

    use NameTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::VARIABLE;
}
