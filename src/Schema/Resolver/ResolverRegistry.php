<?php

namespace Digia\GraphQL\Schema\Resolver;

class ResolverRegistry implements ResolverRegistryInterface
{
    /**
     * @var ResolverInterface[]
     */
    protected $resolverMap;

    /**
     * ResolverMapRegistry constructor.
     * @param array $resolvers
     */
    public function __construct(array $resolvers = [])
    {
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

        if (null === $resolver) {
            return null;
        }

        return $resolver->getResolveMethod($fieldName);
    }

    /**
     * @inheritdoc
     */
    public function getTypeResolver(string $typeName): ?callable
    {
        $resolver = $this->getResolver($typeName);

        if (null === $resolver) {
            return null;
        }

        return $resolver->getTypeResolver();
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
                $resolver = new ArrayResolver($resolver);
            }

            if (\is_string($resolver)) {
                $resolver = new $resolver();
            }

            $this->register($typeName, $resolver);
        }
    }
}
