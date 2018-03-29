<?php

namespace Digia\GraphQL\Language\Node;

class DirectiveNode extends AbstractNode implements NodeInterface, ArgumentsAwareInterface, NameAwareInterface
{
    use NameTrait;
    use ArgumentsTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::DIRECTIVE;
}
