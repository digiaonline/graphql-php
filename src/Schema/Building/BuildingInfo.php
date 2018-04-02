<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationTypeDefinitionNode;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Type\Definition\TypeInterface;

class BuildingInfo
{
    /**
     * @var DocumentNode
     */
    protected $document;

    /**
     * @var SchemaDefinitionNode|null
     */
    protected $schemaDefinition;

    /**
     * @var TypeInterface[]
     */
    protected $typeDefinitionMap;

    /**
     * @var DirectiveDefinitionNode[]
     */
    protected $directiveDefinitions;

    /**
     * @var OperationTypeDefinitionNode[]
     */
    protected $operationTypeDefinitions;

    /**
     * BuildingInfo constructor.
     * @param DocumentNode                  $document
     * @param TypeInterface[]               $typeDefinitionMap
     * @param DirectiveDefinitionNode[]     $directiveDefinitions
     * @param OperationTypeDefinitionNode[] $operationTypeDefinitions
     * @param SchemaDefinitionNode|null     $schemaDefinition
     */
    public function __construct(
        DocumentNode $document,
        array $typeDefinitionMap,
        array $directiveDefinitions,
        array $operationTypeDefinitions,
        ?SchemaDefinitionNode $schemaDefinition = null
    ) {
        $this->document                 = $document;
        $this->typeDefinitionMap        = $typeDefinitionMap;
        $this->directiveDefinitions     = $directiveDefinitions;
        $this->operationTypeDefinitions = $operationTypeDefinitions;
        $this->schemaDefinition         = $schemaDefinition;
    }

    /**
     * @param string $typeName
     * @return TypeInterface|null
     */
    public function getTypeDefinition(string $typeName): ?TypeInterface
    {
        return $this->typeDefinitionMap[$typeName] ?? null;
    }

    /**
     * @param string $operation
     * @return NodeInterface|null
     */
    public function getOperationTypeDefinition(string $operation): ?NodeInterface
    {
        return $this->operationTypeDefinitions[$operation] ?? $this->typeDefinitionMap[\ucfirst($operation)] ?? null;
    }

    /**
     * @return DocumentNode
     */
    public function getDocument(): DocumentNode
    {
        return $this->document;
    }

    /**
     * @return SchemaDefinitionNode|null
     */
    public function getSchemaDefinition(): ?SchemaDefinitionNode
    {
        return $this->schemaDefinition;
    }

    /**
     * @return TypeInterface[]
     */
    public function getTypeDefinitionMap(): array
    {
        return $this->typeDefinitionMap;
    }

    /**
     * @return DirectiveDefinitionNode[]
     */
    public function getDirectiveDefinitions(): array
    {
        return $this->directiveDefinitions;
    }
}
