<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\OperationTypeDefinitionNode;
use Digia\GraphQL\Language\Node\SchemaDefinitionNode;
use Digia\GraphQL\Language\Node\TypeNodeInterface;
use Digia\GraphQL\Type\Definition\TypeInterface;

class BuildInfo
{
    /**
     * @var DocumentNode
     */
    protected $document;

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
     * @var SchemaDefinitionNode|null
     */
    protected $schemaDefinition;

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
     * @return TypeNodeInterface|null
     */
    public function getOperationTypeDefinition(string $operation): ?NodeInterface
    {
        // If we have a schema definition, see if it defines a type, if not we should return null.
        // Otherwise, see if we there is a suitable type available in the type definition map.
        return null !== $this->schemaDefinition
            ? $this->operationTypeDefinitions[$operation] ?? null
            : $this->typeDefinitionMap[\ucfirst($operation)] ?? null;
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
