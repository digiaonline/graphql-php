<?php

namespace Digia\GraphQL\SchemaBuilder;

use Digia\GraphQL\Execution\ValuesResolver;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class DefinitionBuilderCreator implements DefinitionBuilderCreatorInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var ValuesResolver
     */
    protected $valuesResolver;

    /**
     * DefinitionBuilderCreator constructor.
     */
    public function __construct(CacheInterface $cache, ValuesResolver $valuesResolver)
    {
        $this->cache          = $cache;
        $this->valuesResolver = $valuesResolver;
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
        return new DefinitionBuilder($typeDefinitionsMap, $resolverMap, null, $this->cache, $this->valuesResolver);
    }
}
