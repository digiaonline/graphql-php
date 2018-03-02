<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Util\SerializationInterface;

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

    public function getOperationTypesAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->operationTypes);
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind'           => $this->kind,
            'directives'     => $this->getDirectivesAsArray(),
            'operationTypes' => $this->getOperationTypesAsArray(),
            'loc'            => $this->getLocationAsArray(),
        ];
    }
}
