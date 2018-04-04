<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\Resolver\ResolverRegistryInterface;
use Digia\GraphQL\Schema\SchemaInterface;

interface SchemaExtenderInterface
{
    /**
     * @param SchemaInterface                $schema
     * @param DocumentNode                   $document
     * @param ResolverRegistryInterface|null $resolverRegistry
     * @param array                          $options
     * @return SchemaInterface
     */
    public function extend(
        SchemaInterface $schema,
        DocumentNode $document,
        ?ResolverRegistryInterface $resolverRegistry = null,
        array $options = []
    ): SchemaInterface;

    /**
     * @param SchemaInterface                $schema
     * @param DocumentNode                   $document
     * @param ResolverRegistryInterface|null $resolverRegistry
     * @return ExtensionContextInterface
     */
    public function createContext(
        SchemaInterface $schema,
        DocumentNode $document,
        ?ResolverRegistryInterface $resolverRegistry
    ): ExtensionContextInterface;
}
