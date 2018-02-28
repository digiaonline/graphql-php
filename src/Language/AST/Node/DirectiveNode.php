<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\Node\NodeInterface;

class DirectiveNode extends AbstractNode implements NodeInterface
{

    use NameTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::DIRECTIVE;

    /**
     * @var DirectiveNode[]
     */
    protected $arguments;

    /**
     * @return DirectiveNode[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
