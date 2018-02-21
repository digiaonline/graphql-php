<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\ValueTrait;
use Digia\GraphQL\Language\AST\Node\Contract\ValueNodeInterface;

class StringValueNode extends AbstractNode implements ValueNodeInterface
{

    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::STRING;

    /**
     * @var bool
     */
    protected $block;

    /**
     * @return bool
     */
    public function isBlock(): bool
    {
        return $this->block;
    }
}
