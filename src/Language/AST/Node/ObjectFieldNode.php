<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\ValueLiteralTrait;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

class ObjectFieldNode extends AbstractNode implements NodeInterface
{

    use NameTrait;
    use ValueLiteralTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::OBJECT_FIELD;
}
