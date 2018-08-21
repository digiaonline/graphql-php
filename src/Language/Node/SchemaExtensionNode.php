<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class SchemaExtensionNode extends AbstractNode implements TypeSystemExtensionNodeInterface, DirectivesAwareInterface
{
    use DirectivesTrait;

    /**
     * @var OperationTypeDefinitionNode[]
     */
    protected $operationTypes;

    /**
     * SchemaExtensionNode constructor.
     * @param DirectiveNode[]               $directives
     * @param OperationTypeDefinitionNode[] $operationTypes
     * @param Location|null                 $location
     */
    public function __construct(array $directives, array $operationTypes, ?Location $location)
    {
        parent::__construct(NodeKindEnum::SCHEMA_EXTENSION, $location);

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
    public function getOperationTypesAST(): array
    {
        return \array_map(function (OperationTypeDefinitionNode $node) {
            return $node->toAST();
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
    public function toAST(): array
    {
        return [
            'kind'           => $this->kind,
            'directives'     => $this->getDirectivesAST(),
            'operationTypes' => $this->getOperationTypesAST(),
            'loc'            => $this->getLocationAST(),
        ];
    }
}
