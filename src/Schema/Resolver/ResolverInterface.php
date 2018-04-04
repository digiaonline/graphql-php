<?php

namespace Digia\GraphQL\Schema\Resolver;

interface ResolverInterface
{
    /**
     * @param string $fieldName
     * @return callable|null
     */
    public function getResolveMethod(string $fieldName): ?callable;
}
