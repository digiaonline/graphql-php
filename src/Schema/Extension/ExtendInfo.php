<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\SchemaExtensionNode;
use Digia\GraphQL\Language\Node\TypeSystemDefinitionNodeInterface;
use Digia\GraphQL\Schema\Schema;

class ExtendInfo
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var DocumentNode
     */
    protected $document;

    /**
     * @var TypeSystemDefinitionNodeInterface[]
     */
    protected $typeDefinitionMap;

    /**
     * @var InterfaceTypeExtensionNode[][]|ObjectTypeExtensionNode[][]
     */
    protected $typeExtensionsMap;

    /**
     * @var DirectiveDefinitionNode[]
     */
    protected $directiveDefinitions;

    /**
     * @var SchemaExtensionNode[]
     */
    protected $schemaExtensions;

    /**
     * ExtensionInfo constructor.
     * @param Schema                                                     $schema
     * @param DocumentNode                                               $document
     * @param TypeSystemDefinitionNodeInterface[]                        $typeDefinitionMap
     * @param InterfaceTypeExtensionNode[][]|ObjectTypeExtensionNode[][] $typeExtensionsMap
     * @param DirectiveDefinitionNode[]                                  $directiveDefinitions
     * @param SchemaExtensionNode[]                                      $schemaExtensions
     */
    public function __construct(
        Schema $schema,
        DocumentNode $document,
        array $typeDefinitionMap,
        array $typeExtensionsMap,
        array $directiveDefinitions,
        array $schemaExtensions
    ) {
        $this->schema               = $schema;
        $this->document             = $document;
        $this->typeDefinitionMap    = $typeDefinitionMap;
        $this->typeExtensionsMap    = $typeExtensionsMap;
        $this->directiveDefinitions = $directiveDefinitions;
        $this->schemaExtensions     = $schemaExtensions;
    }

    /**
     * @param string $typeName
     * @return bool
     */
    public function hasTypeExtensions(string $typeName): bool
    {
        return isset($this->typeExtensionsMap[$typeName]);
    }

    /**
     * @param string $typeName
     * @return ObjectTypeExtensionNode[]|InterfaceTypeExtensionNode[]
     */
    public function getTypeExtensions(string $typeName): array
    {
        return $this->typeExtensionsMap[$typeName] ?? [];
    }

    /**
     * @return Schema
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @return DocumentNode
     */
    public function getDocument(): DocumentNode
    {
        return $this->document;
    }

    /**
     * @return bool
     */
    public function hasTypeDefinitionMap(): bool
    {
        return !empty($this->typeDefinitionMap);
    }

    /**
     * @return TypeSystemDefinitionNodeInterface[]
     */
    public function getTypeDefinitionMap(): array
    {
        return $this->typeDefinitionMap;
    }

    /**
     * @return bool
     */
    public function hasTypeExtensionsMap(): bool
    {
        return !empty($this->typeExtensionsMap);
    }

    /**
     * @return InterfaceTypeExtensionNode[][]|ObjectTypeExtensionNode[][]
     */
    public function getTypeExtensionsMap()
    {
        return $this->typeExtensionsMap;
    }

    /**
     * @return bool
     */
    public function hasDirectiveDefinitions(): bool
    {
        return !empty($this->directiveDefinitions);
    }

    /**
     * @return DirectiveDefinitionNode[]
     */
    public function getDirectiveDefinitions(): array
    {
        return $this->directiveDefinitions;
    }

    /**
     * @return bool
     */
    public function hasSchemaExtensions(): bool
    {
        return !empty($this->schemaExtensions);
    }

    /**
     * @return SchemaExtensionNode[]
     */
    public function getSchemaExtensions(): array
    {
        return $this->schemaExtensions;
    }
}
