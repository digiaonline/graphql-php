<?php

namespace Digia\GraphQL\Schema\Resolver;

use Digia\GraphQL\Execution\ResolveInfo;

class ResolverRegistry implements ResolverRegistryInterface
{
    /**
     * @var ResolverInterface[]
     */
    protected $resolverMap;

    /**
     * @var ResolverMiddlewareInterface[]|null
     */
    protected $middleware;

    /**
     * ResolverRegistry constructor.
     * @param ResolverInterface[]                $resolvers
     * @param ResolverMiddlewareInterface[]|null $middleware
     */
    public function __construct(array $resolvers = [], ?array $middleware = null)
    {
        $this->middleware = $middleware;

        $this->registerResolvers($resolvers);
    }

    /**
     * @inheritdoc
     */
    public function register(string $typeName, ResolverInterface $resolver): void
    {
        $this->resolverMap[$typeName] = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function getFieldResolver(string $typeName, string $fieldName): ?callable
    {
        $resolver = $this->getResolver($typeName);

        $resolver = $resolver instanceof ResolverCollection
            ? $resolver->getResolveCallback()($fieldName)
            : $resolver;

        $resolveCallback = $resolver instanceof ResolverInterface
            ? $resolver->getResolveCallback()
            : $resolver;

        if (null === $resolveCallback) {
            return null;
        }

        if (null !== $this->middleware) {
            return $this->applyMiddleware($resolveCallback, \array_reverse($this->middleware));
        }

        return $resolveCallback;
    }

    /**
     * @inheritdoc
     */
    public function getTypeResolver(string $typeName): ?callable
    {
        $resolver = $this->getResolver($typeName);

        if ($resolver instanceof ResolverInterface) {
            return $resolver->getTypeResolver();
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getResolver(string $typeName): ?ResolverInterface
    {
        return $this->resolverMap[$typeName] ?? null;
    }

    /**
     * @param array $resolvers
     */
    protected function registerResolvers(array $resolvers): void
    {
        foreach ($resolvers as $typeName => $resolver) {
            if (\is_array($resolver)) {
                $resolver = new ResolverCollection($resolver);
            }

            $this->register($typeName, $resolver);
        }
    }

    /**
     * @param callable $resolveCallback
     * @param array    $middleware
     * @return callable
     */
    protected function applyMiddleware(callable $resolveCallback, array $middleware): callable
    {
        return \array_reduce(
            $middleware,
            function (callable $resolveCallback, ResolverMiddlewareInterface $middleware) {
                return function ($rootValue, array $arguments, $context = null, ?ResolveInfo $info = null) use (
                    $resolveCallback,
                    $middleware
                ) {
                    return $middleware->resolve($resolveCallback, $rootValue, $arguments, $context, $info);
                };
            },
            $resolveCallback
        );
    }
}
