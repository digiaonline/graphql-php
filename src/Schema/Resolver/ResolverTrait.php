<?php

namespace Digia\GraphQL\Schema\Resolver;

use Digia\GraphQL\Execution\ResolveInfo;

trait ResolverTrait
{
    /**
     * @return callable|null
     */
    public function getTypeResolver(): ?callable
    {
        return function ($rootValue, $context = null, ?ResolveInfo $info = null) {
            return $this->resolveType($rootValue, $context, $info);
        };
    }

    /**
     * @param mixed            $rootValue
     * @param mixed            $context
     * @param ResolveInfo|null $info
     * @return callable|null
     */
    public function resolveType($rootValue, $context = null, ?ResolveInfo $info = null): ?callable
    {
        // Override this method when your resolver returns an interface or an union type.
        return null;
    }

    /**
     * @return array|null
     */
    public function getMiddleware(): ?array
    {
        return null;
    }
}
