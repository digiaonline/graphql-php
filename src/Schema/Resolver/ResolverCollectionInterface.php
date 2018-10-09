<?php

namespace Digia\GraphQL\Schema\Resolver;

interface ResolverCollectionInterface extends ResolverInterface
{
    /**
     * @param string $fieldName
     * @return callable|null
     */
    public function getResolver(string $fieldName): ?callable;
}
