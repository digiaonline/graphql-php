<?php

namespace Digia\GraphQL\Schema;

class ResolverMapRegistry implements ResolverRegistryInterface
{
    /**
     * @var array
     */
    protected $resolverMap;

    /**
     * ResolverMapRegistry constructor.
     * @param array $resolverMap
     */
    public function __construct(array $resolverMap = [])
    {
        $this->resolverMap = $resolverMap;
    }

    /**
     * @param string   $typeName
     * @param string   $fieldName
     * @param callable $resolver
     */
    public function register(string $typeName, string $fieldName, callable $resolver): void
    {
        if (!isset($this->resolverMap[$typeName])) {
            $this->resolverMap[$typeName] = [];
        }

        $this->resolverMap[$typeName][$fieldName] = $resolver;
    }

    /**
     * @param string $typeName
     * @param string $fieldName
     * @return callable
     */
    public function lookup(string $typeName, string $fieldName): ?callable
    {
        $resolverMap = $this->resolverMap[$typeName] ?? [];
        return $resolverMap[$fieldName] ?? null;
    }
}
