<?php

namespace Digia\GraphQL\Schema\Resolver;

use Digia\GraphQL\Execution\ResolveInfo;

interface ResolverMiddlewareInterface
{
    /**
     * @param callable         $resolveCallback
     * @param mixed            $rootValue
     * @param array            $arguments
     * @param mixed            $context
     * @param ResolveInfo|null $info
     * @return mixed
     */
    public function resolve(
        callable $resolveCallback,
        $rootValue,
        array $arguments,
        $context = null,
        ?ResolveInfo $info = null
    );
}
