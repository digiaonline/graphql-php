<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

class DocumentNode extends AbstractNode implements NodeInterface
{

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::DOCUMENT;

    /**
     * @var DefinitionNodeInterface[]
     */
    protected $definitions;

    /**
     * @return DefinitionNodeInterface[]
     */
    public function getDefinition()
    {
        return $this->definitions;
    }
}
