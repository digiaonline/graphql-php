<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Contract\ValueNodeInterface;

class VariableNode extends AbstractNode implements ValueNodeInterface
{

    use NameTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::VARIABLE;
}
