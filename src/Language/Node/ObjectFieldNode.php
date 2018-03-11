<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class ObjectFieldNode extends AbstractNode implements NodeInterface
{

    use NameTrait;
    use ValueLiteralTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::OBJECT_FIELD;
}
