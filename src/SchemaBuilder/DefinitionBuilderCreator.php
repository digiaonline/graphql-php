<?php

namespace Digia\GraphQL\SchemaBuilder;

use Psr\SimpleCache\CacheInterface;

class DefinitionBuilderCreator implements DefinitionBuilderCreatorInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * DefinitionBuilderCreator constructor.
     */
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function create(
        array $typeDefinitionsMap,
        ?callable $resolveTypeFunction = null,
        ?ResolverRegistryInterface $resolverRegistry = null
    ): DefinitionBuilderInterface {
        return new DefinitionBuilder(
            $typeDefinitionsMap,
            $resolverRegistry ?? new ResolverMapRegistry(),
            null,
            $this->cache
        );
    }
}
