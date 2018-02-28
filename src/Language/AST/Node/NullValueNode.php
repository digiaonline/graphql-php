<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\ValueNodeInterface;

class NullValueNode extends AbstractNode implements ValueNodeInterface
{

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::NULL;
}
