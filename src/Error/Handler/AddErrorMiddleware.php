<?php

namespace Digia\GraphQL\Error\Handler;

use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ExecutionException;

class AddErrorMiddleware implements ErrorMiddlewareInterface
{
    /**
     * @inheritdoc
     */
    public function handle(ExecutionException $exception, ExecutionContext $context, callable $next)
    {
        $context->addError($exception);

        return $next($exception, $context);
    }
}
