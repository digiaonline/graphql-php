<?php

namespace Digia\GraphQL\SchemaBuilder;

interface ResolverRegistryInterface
{
    /**
     * @param string   $typeName
     * @param string   $fieldName
     * @param callable $resolver
     */
    public function register(string $typeName, string $fieldName, callable $resolver): void;

    /**
     * @param string $typeName
     * @param string $fieldName
     * @return callable|null
     */
    public function lookup(string $typeName, string $fieldName): ?callable;
}
