<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class EnumValueNode extends AbstractNode implements ValueNodeInterface
{

    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::ENUM;
}
