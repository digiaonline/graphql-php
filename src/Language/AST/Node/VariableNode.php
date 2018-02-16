<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\KindTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\LocationTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;
use Digia\GraphQL\ConfigObject;

class VariableNode extends ConfigObject implements NodeInterface
{
    use KindTrait;
    use LocationTrait;
    use NameTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::VARIABLE;
}
