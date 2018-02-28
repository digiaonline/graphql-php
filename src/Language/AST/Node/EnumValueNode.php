<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\ValueLiteralTrait;
use Digia\GraphQL\Language\AST\Node\ValueNodeInterface;

class EnumValueNode extends AbstractNode implements ValueNodeInterface
{

    use ValueLiteralTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::ENUM;
}
