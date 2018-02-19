<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Contract\TypeNodeInterface;

class NamedTypeNode extends AbstractNode implements TypeNodeInterface
{

    use NameTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::NAMED_TYPE;
}
