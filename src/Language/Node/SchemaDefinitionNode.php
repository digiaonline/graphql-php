<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Util\SerializationInterface;

class SchemaDefinitionNode extends AbstractNode implements TypeSystemDefinitionNodeInterface
{

    use DirectivesTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::SCHEMA_DEFINITION;

    /**
     * @var array|OperationTypeDefinitionNode[]
     */
    protected $operationTypes;

    /**
     * @return array|OperationTypeDefinitionNode[]
     */
    public function getOperationTypes(): array
    {
        return $this->operationTypes;
    }

    /**
     * @param array|OperationTypeDefinitionNode[] $operationTypes
     *
     * @return $this
     */
    public function setOperationTypes(array $operationTypes)
    {
        $this->operationTypes = $operationTypes;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'directives' => $this->getDirectivesAsArray(),
            'operationTypes' => $this->getOperationTypesAsArray(),
            'loc' => $this->getLocationAsArray(),
        ];
    }

    /**
     * @return array
     */
    public function getOperationTypesAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->operationTypes);
    }
}
