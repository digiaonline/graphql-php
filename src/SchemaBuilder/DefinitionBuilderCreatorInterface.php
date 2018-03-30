<?php

namespace Digia\GraphQL\SchemaBuilder;

interface DefinitionBuilderCreatorInterface
{
    /**
     * @param array                          $typeDefinitionsMap
     * @param callable|null                  $resolveTypeFunction
     * @param ResolverRegistryInterface|null $resolverRegistry
     * @return DefinitionBuilderInterface
     */
    public function create(
        array $typeDefinitionsMap,
        ?callable $resolveTypeFunction = null,
        ?ResolverRegistryInterface $resolverRegistry = null
    ): DefinitionBuilderInterface;
}
