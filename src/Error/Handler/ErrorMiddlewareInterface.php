<?php

namespace Digia\GraphQL\Error\Handler;

use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ExecutionException;

interface ErrorMiddlewareInterface
{
    /**
     * @param ExecutionException $exception
     * @param ExecutionContext   $context
     * @return mixed
     */
    public function handle(ExecutionException $exception, ExecutionContext $context, callable $next);
}
