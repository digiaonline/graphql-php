<?php

namespace Digia\GraphQL\Language\Node;

class EnumValueNode extends AbstractNode implements ValueNodeInterface
{
    use ValueTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::ENUM;
}
