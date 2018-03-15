<?php

namespace Digia\GraphQL\Language\SchemaBuilder;

use Digia\GraphQL\Execution\Resolver\ResolveInfo;

interface ResolverInterface
{
    /**
     * @param mixed       $rootValue
     * @param array       $arguments
     * @param mixed       $contextValue
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve($rootValue, array $arguments, $contextValue, ResolveInfo $info);
}
