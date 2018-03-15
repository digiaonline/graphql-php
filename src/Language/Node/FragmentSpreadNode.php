<?php

namespace Digia\GraphQL\Language\Node;

class FragmentSpreadNode extends AbstractNode implements NodeInterface, NameAwareInterface
{
    use NameTrait;
    use DirectivesTrait;
    use SelectionSetTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::FRAGMENT_SPREAD;
}
