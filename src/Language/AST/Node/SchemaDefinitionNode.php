<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\Behavior\DirectivesTrait;
use Digia\GraphQL\Language\AST\Node\Contract\TypeSystemDefinitionNodeInterface;

class SchemaDefinitionNode extends AbstractNode implements TypeSystemDefinitionNodeInterface
{

    use DirectivesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::SCHEMA_DEFINITION;

    /**
     * @var OperationTypeDefinitionNode[]
     */
    protected $operationTypes;

    /**
     * @return OperationTypeDefinitionNode[]
     */
    public function getOperationTypes(): array
    {
        return $this->operationTypes;
    }
}
