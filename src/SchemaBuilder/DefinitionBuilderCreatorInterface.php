<?php

namespace Digia\GraphQL\SchemaBuilder;

interface DefinitionBuilderCreatorInterface
{
    /**
     * @param array                     $typeDefinitionsMap
     * @param ResolverRegistryInterface $resolverRegistry
     * @param callable|null             $resolveTypeFunction
     * @return DefinitionBuilderInterface
     */
    public function create(
        array $typeDefinitionsMap,
        ResolverRegistryInterface $resolverRegistry,
        ?callable $resolveTypeFunction = null
    ): DefinitionBuilderInterface;
}
