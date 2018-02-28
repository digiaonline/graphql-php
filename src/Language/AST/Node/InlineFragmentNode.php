<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\SelectionSetTrait;
use Digia\GraphQL\Language\AST\Node\TypeConditionTrait;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

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
