<?php

namespace Digia\GraphQL\Schema\Extension;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\Resolver\ResolverRegistryInterface;
use Digia\GraphQL\Schema\Schema;

interface SchemaExtenderInterface
{
    /**
     * @param Schema                         $schema
     * @param DocumentNode                   $document
     * @param ResolverRegistryInterface|null $resolverRegistry
     * @param array                          $options
     * @return Schema
     */
    public function extend(
        Schema $schema,
        DocumentNode $document,
        ?ResolverRegistryInterface $resolverRegistry = null,
        array $options = []
    ): Schema;

    /**
     * @param Schema                         $schema
     * @param DocumentNode                   $document
     * @param ResolverRegistryInterface|null $resolverRegistry
     * @return ExtensionContextInterface
     */
    public function createContext(
        Schema $schema,
        DocumentNode $document,
        ?ResolverRegistryInterface $resolverRegistry
    ): ExtensionContextInterface;
}
