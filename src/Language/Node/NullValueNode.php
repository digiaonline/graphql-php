<?php

namespace Digia\GraphQL\Language\Node;

class NullValueNode extends AbstractNode implements ValueNodeInterface
{

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::NULL;
}
