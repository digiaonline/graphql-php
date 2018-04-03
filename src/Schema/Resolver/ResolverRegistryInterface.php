<?php

namespace Digia\GraphQL\Schema\Resolver;

interface ResolverRegistryInterface
{
    /**
     * @param string                $typeName
     * @param callable|array|string $resolver
     */
    public function register(string $typeName, $resolver): void;

    /**
     * @param string $typeName
     * @param string $fieldName
     * @return callable|null
     */
    public function lookup(string $typeName, string $fieldName): ?callable;
}
