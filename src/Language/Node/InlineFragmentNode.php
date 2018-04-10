<?php

namespace Digia\GraphQL\Language\Node;

class InlineFragmentNode extends AbstractNode implements FragmentNodeInterface
{

    use DirectivesTrait;
    use TypeConditionTrait;
    use SelectionSetTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::INLINE_FRAGMENT;
}
