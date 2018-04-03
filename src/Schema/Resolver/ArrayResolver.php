<?php

namespace Digia\GraphQL\Schema\Resolver;

class ArrayResolver implements ResolverInterface
{
    /**
     * @var callable[]
     */
    protected $resolvers;

    /**
     * MapResolver constructor.
     * @param callable[] $resolvers
     */
    public function __construct(array $resolvers)
    {
        $this->resolvers = $resolvers;
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
    public function getResolveMethod(string $fieldName): ?callable
    {
        return $this->resolvers[$fieldName] ?? null;
    }
}
