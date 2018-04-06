<?php

namespace Digia\GraphQL\Schema\Resolver;

use Digia\GraphQL\Execution\ResolveInfo;

class AbstractResolver implements ResolverInterface
{

    /**
     * @inheritdoc
     */
    public function getResolveMethod(string $fieldName): ?callable
    {
        $resolveMethod = 'resolve'.\ucfirst($fieldName);

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

    /**
     * @param mixed $rootValue
     * @param mixed $contextValues
     * @param ResolveInfo $info
     *
     * @return callable|null
     */
    protected function resolveType(
        $rootValue,
        $contextValues,
        ResolveInfo $info
    ): ?callable
    {
        return null;
    }
}
