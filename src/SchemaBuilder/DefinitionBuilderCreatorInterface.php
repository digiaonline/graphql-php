<?php

namespace Digia\GraphQL\SchemaBuilder;

interface DefinitionBuilderCreatorInterface
{
    /**
     * @param array         $typeDefinitionsMap
     * @param array         $resolverMap
     * @param callable|null $resolveTypeFunction
     * @return DefinitionBuilderInterface
     */
    public function create(
        array $typeDefinitionsMap,
        array $resolverMap,
        ?callable $resolveTypeFunction = null
    ): DefinitionBuilderInterface;
}
