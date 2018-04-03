<?php

namespace Digia\GraphQL\Schema\Resolver;

class ResolverRegistry implements ResolverRegistryInterface
{
    /**
     * @var array
     */
    protected $resolverMap;

    /**
     * ResolverMapRegistry constructor.
     *
     * @param array $resolverMap
     */
    public function __construct(array $resolverMap = [])
    {
        $this->resolverMap = $resolverMap;
    }

    /**
     * @inheritdoc
     */
    public function register(string $typeName, $resolver): void
    {
        $this->resolverMap[$typeName] = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function lookup(string $typeName, string $fieldName): ?callable
    {
        $resolver = $this->resolverMap[$typeName] ?? null;

        if (\is_array($resolver) && isset($resolver[$fieldName])) {
            return $resolver[$fieldName];
        }

        if (\is_string($resolver)) {
            $resolver = new $resolver();
        }

        if ($resolver instanceof ResolverInterface) {
            return $resolver->getResolveMethod($fieldName);
        }

        return null;
    }
}
