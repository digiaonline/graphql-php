<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\ResolverRegistryInterface;
use Digia\GraphQL\Schema\SchemaInterface;

interface SchemaBuilderInterface
{
    /**
     * @param DocumentNode              $document
     * @param ResolverRegistryInterface $resolverRegistry
     * @param array                     $options
     * @return SchemaInterface
     */
    public function build(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry,
        array $options = []
    ): SchemaInterface;

    /**
     * @param DocumentNode              $document
     * @param ResolverRegistryInterface $resolverRegistry
     * @return BuilderContextInterface
     */
    public function createContext(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry
    ): BuilderContextInterface;
}
