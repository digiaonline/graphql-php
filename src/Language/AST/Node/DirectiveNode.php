<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\KindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

class DirectiveNode extends AbstractNode implements NodeInterface
{

    use NameTrait;

    /**
     * @var string
     */
    protected $kind = KindEnum::DIRECTIVE;

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
