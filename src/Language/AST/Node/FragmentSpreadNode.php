<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\Behavior\SelectionSetTrait;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

class FragmentSpreadNode extends AbstractNode implements NodeInterface
{

    use NameTrait;
    use DirectivesTrait;
    use SelectionSetTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::FRAGMENT_SPREAD;
}
