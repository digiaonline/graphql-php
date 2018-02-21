<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\SelectionSetTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\TypeConditionTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

class InlineFragmentNode extends AbstractNode implements NodeInterface
{

    use DirectivesTrait;
    use TypeConditionTrait;
    use SelectionSetTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::INLINE_FRAGMENT;
}
