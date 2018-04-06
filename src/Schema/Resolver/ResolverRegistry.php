<?php

namespace Digia\GraphQL\Schema\Resolver;

use Digia\GraphQL\Error\ResolutionException;

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

        if ($resolver instanceof ResolverInterface) {
            return $resolver->getResolveMethod($fieldName);
        }

        throw new ResolutionException(\sprintf('Failed to resolve field "%s" for type "%s".', $fieldName, $typeName));
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

        if ($resolver instanceof ResolverInterface) {
            return $resolver->getTypeResolver();
        }

        throw new ResolutionException(\sprintf('Failed to resolve type for "%s".', $typeName));
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
