<?php

namespace Digia\GraphQL\Error\Handler;

use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ExecutionException;

abstract class AbstractErrorMiddleware implements ErrorMiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function handleError(\Throwable $exception, callable $next)
    {
    }

    /**
     * @inheritdoc
     */
    public function handleExecutionError(ExecutionException $exception, ExecutionContext $context, callable $next)
    {
    }
}
