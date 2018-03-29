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
        array $resolverMap,
        ?callable $resolveTypeFunction = null
    ): DefinitionBuilderInterface {
        return new DefinitionBuilder($typeDefinitionsMap, $resolverMap, null, $this->cache);
    }
}
