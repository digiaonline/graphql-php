<?php

namespace Digia\GraphQL\Schema\Resolver;

use Digia\GraphQL\Execution\ResolveInfo;

abstract class AbstractFieldResolver implements ResolverInterface
{
    use ResolverTrait;

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
}
