<?php

namespace Digia\GraphQL\SchemaBuilder;

use Digia\GraphQL\Language\Node\DocumentNode;

interface BuilderContextCreatorInterface
{
    /**
     * @param DocumentNode              $document
     * @param ResolverRegistryInterface $resolverRegistry
     * @return BuilderContextInterface
     */
    public function create(
        DocumentNode $document,
        ResolverRegistryInterface $resolverRegistry
    ): BuilderContextInterface;
}
