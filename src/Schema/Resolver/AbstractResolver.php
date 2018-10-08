<?php

namespace Digia\GraphQL\Schema\Resolver;

use Digia\GraphQL\Execution\ResolveInfo;

abstract class AbstractResolver implements ClassResolverInterface
{
    /**
     * @param mixed       $rootValue
     * @param mixed       $contextValues
     * @param ResolveInfo $info
     * @return callable|null
     */
    abstract public function resolveType($rootValue, $contextValues, ResolveInfo $info): ?callable;

    /**
     * @inheritdoc
     */
    public function beforeResolve(
        callable $resolveCallback,
        $rootValue,
        array $args,
        $contextValues = null,
        ?ResolveInfo $info = null
    ) {
        // Override this method to perform logic before the resolver is invoked.
        return $resolveCallback($rootValue, $args, $contextValues, $info);
    }

    /**
     * @inheritdoc
     */
    public function afterResolve(
        $result,
        $rootValue,
        array $args,
        $contextValues = null,
        ?ResolveInfo $info = null
    ) {
        // Override this method to perform logic after the resolver is invoked.
        return $result;
    }


    /**
     * @inheritdoc
     */
    public function getResolveCallback(string $fieldName): ?callable
    {
        $resolveMethod = 'resolve' . \ucfirst($fieldName);

        if (\method_exists($this, $resolveMethod)) {
            return [$this, $resolveMethod];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getTypeResolver(): ?callable
    {
        return function ($rootValue, $contextValues, ResolveInfo $info) {
            return $this->resolveType($rootValue, $contextValues, $info);
        };
    }
}
