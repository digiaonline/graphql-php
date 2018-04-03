<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Language\Node\DirectiveDefinitionNode;
use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Language\Node\InterfaceTypeExtensionNode;
use Digia\GraphQL\Language\Node\ObjectTypeExtensionNode;
use Digia\GraphQL\Language\Node\TypeDefinitionNodeInterface;
use Digia\GraphQL\Schema\SchemaInterface;

class ExtendInfo
{
    /**
     * @var SchemaInterface
     */
    protected $schema;

    /**
     * @var DocumentNode
     */
    protected $document;

    /**
     * @var TypeDefinitionNodeInterface[]
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
     * ExtensionInfo constructor.
     * @param SchemaInterface                                            $schema
     * @param DocumentNode                                               $document
     * @param TypeDefinitionNodeInterface[]                              $typeDefinitionMap
     * @param InterfaceTypeExtensionNode[][]|ObjectTypeExtensionNode[][] $typeExtensionsMap
     * @param DirectiveDefinitionNode[]                                  $directiveDefinitions
     */
    public function __construct(
        SchemaInterface $schema,
        DocumentNode $document,
        array $typeDefinitionMap,
        array $typeExtensionsMap,
        array $directiveDefinitions
    ) {
        $this->schema               = $schema;
        $this->document             = $document;
        $this->typeDefinitionMap    = $typeDefinitionMap;
        $this->typeExtensionsMap    = $typeExtensionsMap;
        $this->directiveDefinitions = $directiveDefinitions;
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
     * @return InterfaceTypeExtensionNode[]|ObjectTypeExtensionNode[]|null
     */
    public function getTypeExtensions(string $typeName): ?array
    {
        return $this->typeExtensionsMap[$typeName] ?? null;
    }

    /**
     * @return SchemaInterface
     */
    public function getSchema(): SchemaInterface
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
     * @return TypeDefinitionNodeInterface[]
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
}
