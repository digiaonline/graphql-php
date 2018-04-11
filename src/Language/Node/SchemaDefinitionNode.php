<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;
use Digia\GraphQL\Util\SerializationInterface;

class SchemaDefinitionNode extends AbstractNode implements TypeSystemDefinitionNodeInterface
{
    use DirectivesTrait;

    /**
     * @var OperationTypeDefinitionNode[]
     */
    protected $operationTypes;

    /**
     * SchemaDefinitionNode constructor.
     *
     * @param DirectiveNode[]               $directives
     * @param OperationTypeDefinitionNode[] $operationTypes
     * @param Location|null                 $location
     */
    public function __construct(array $directives, array $operationTypes, ?Location $location)
    {
        parent::__construct(NodeKindEnum::SCHEMA_DEFINITION, $location);

        $this->directives     = $directives;
        $this->operationTypes = $operationTypes;
    }

    /**
     * @return OperationTypeDefinitionNode[]
     */
    public function getOperationTypes(): array
    {
        return $this->operationTypes;
    }

    /**
     * @return array
     */
    public function getOperationTypesAsArray(): array
    {
        return \array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->operationTypes);
    }

    /**
     * @param OperationTypeDefinitionNode[] $operationTypes
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
            'kind'           => $this->kind,
            'directives'     => $this->getDirectivesAsArray(),
            'operationTypes' => $this->getOperationTypesAsArray(),
            'loc'            => $this->getLocationAsArray(),
        ];
    }
}
