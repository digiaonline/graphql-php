<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

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
