<?php

namespace Digia\GraphQL\Execution\Contract;

use Digia\GraphQL\Execution\ResolveInfo;

interface ResolverInterface
{

    /**
     * @param mixed       $source
     * @param array       $args
     * @param mixed       $context
     * @param ResolveInfo $info
     * @return mixed
     */
    public function resolve($source, $args, $context, ResolveInfo $info);
}
