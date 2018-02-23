<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\Behavior\NameTrait;
use Digia\GraphQL\Language\AST\Node\Behavior\TypeTrait;
use Digia\GraphQL\Language\AST\Node\Contract\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\NodeKindEnum;

class OperationTypeDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{

    use NameTrait;
    use TypeTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::OPERATION_TYPE_DEFINITION;

    /**
     * @var string
     */
    protected $operation;

    /**
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }
}
