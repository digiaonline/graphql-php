<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

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
