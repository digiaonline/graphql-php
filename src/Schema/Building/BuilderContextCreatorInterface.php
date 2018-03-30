<?php

namespace Digia\GraphQL\Schema\Building;

use Digia\GraphQL\Language\Node\DocumentNode;
use Digia\GraphQL\Schema\ResolverRegistryInterface;

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
