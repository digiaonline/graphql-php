<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Node\NodeKindEnum;

class DirectiveNode extends AbstractNode implements NodeInterface
{

    use NameTrait;
    use ArgumentsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::DIRECTIVE;
}
