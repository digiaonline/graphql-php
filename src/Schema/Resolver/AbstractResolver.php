<?php

namespace Digia\GraphQL\Schema\Resolver;

use Digia\GraphQL\Execution\ResolveInfo;

abstract class AbstractResolver implements ResolverInterface
{
    /**
     * @param mixed            $rootValue
     * @param array            $arguments
     * @param mixed            $context
     * @param ResolveInfo|null $info
     * @return mixed
     */
    abstract public function resolve($rootValue, array $arguments, $context = null, ?ResolveInfo $info = null);

    /**
     * @return mixed
     */
    public function __invoke()
    {
        return $this->resolve(...\func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getResolveCallback(): ?callable
    {
        return function ($rootValue, array $arguments, $context = null, ?ResolveInfo $info = null) {
            return $this->resolve($rootValue, $arguments, $context, $info);
        };
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getMiddleware(): ?array
    {
        return null;
    }
}
