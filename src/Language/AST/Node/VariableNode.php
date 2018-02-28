<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\ValueNodeInterface;

class VariableNode extends AbstractNode implements ValueNodeInterface
{

    use NameTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::VARIABLE;
}
