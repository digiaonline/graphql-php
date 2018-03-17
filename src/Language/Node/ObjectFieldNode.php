<?php

namespace Digia\GraphQL\Language\Node;

class ObjectFieldNode extends AbstractNode implements NodeInterface
{
    use NameTrait;
    use ValueLiteralTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::OBJECT_FIELD;
}
