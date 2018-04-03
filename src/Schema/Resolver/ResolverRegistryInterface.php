<?php

namespace Digia\GraphQL\Schema\Resolver;

interface ResolverRegistryInterface
{
    /**
     * @param string            $typeName
     * @param ResolverInterface $resolver
     */
    public function register(string $typeName, ResolverInterface $resolver): void;

    /**
     * @param string $typeName
     * @param string $fieldName
     * @return callable|null
     */
    public function lookup(string $typeName, string $fieldName): ?callable;
}
