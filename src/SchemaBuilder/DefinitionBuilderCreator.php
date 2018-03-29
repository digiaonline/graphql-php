<?php

namespace Digia\GraphQL\SchemaBuilder;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

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
     * @throws InvalidArgumentException
     */
    public function create(
        array $typeDefinitionsMap,
        ResolverRegistryInterface $resolverRegistry,
        ?callable $resolveTypeFunction = null
    ): DefinitionBuilderInterface {
        return new DefinitionBuilder($typeDefinitionsMap, $resolverRegistry, null, $this->cache);
    }
}
