<?php

namespace Digia\GraphQL\Schema\Resolver;

class ResolverCollection implements ResolverInterface
{
    protected const TYPE_RESOLVER_KEY = '__resolveType';

    /**
     * @var callable[]
     */
    protected $resolvers;

    /**
     * ResolverCollection constructor.
     * @param callable[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->registerResolvers($resolvers);
    }

    /**
     * @param string   $fieldName
     * @param callable $resolver
     */
    public function addResolver(string $fieldName, callable $resolver)
    {
        $this->resolvers[$fieldName] = $resolver;
    }

    /**
     * @param string $fieldName
     * @return callable|null
     */
    public function getResolver(string $fieldName): ?callable
    {
        return $this->resolvers[$fieldName] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getResolveCallback(): ?callable
    {
        return function ($fieldName) {
            $resolver = $this->getResolver($fieldName);

            if ($resolver instanceof ResolverInterface) {
                return $resolver->getResolveCallback();
            }

            if ($resolver instanceof \Closure) {
                return $resolver;
            }

            return null;
        };
    }

    /**
     * @inheritdoc
     */
    public function getTypeResolver(): ?callable
    {
        return $this->resolvers[static::TYPE_RESOLVER_KEY] ?? null;
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

            $this->addResolver($typeName, $resolver);
        }
    }
}
