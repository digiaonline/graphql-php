<?php

namespace Digia\GraphQL\Execution\Resolver;

use Digia\GraphQL\Execution\ExecutionEnvironment;

interface ResolverInterface
{
    /**
     * @param ExecutionEnvironment $environment
     * @return mixed
     */
    public function resolve(ExecutionEnvironment $environment);
}
