<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class NullValueNode extends AbstractNode implements ValueNodeInterface
{

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::NULL;
}
