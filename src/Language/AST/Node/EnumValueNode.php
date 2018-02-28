<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;

class EnumValueNode extends AbstractNode implements ValueNodeInterface
{

    use ValueLiteralTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::ENUM;
}
