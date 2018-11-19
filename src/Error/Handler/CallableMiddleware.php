<?php

namespace Digia\GraphQL\Error\Handler;

use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ExecutionException;

class CallableMiddleware extends AbstractErrorMiddleware
{
    /**
     * @var callable
     */
    protected $handleCallback;

    /**
     * CallableMiddleware constructor.
     * @param callable $handleCallback
     */
    public function __construct(callable $handleCallback)
    {
        $this->handleCallback = $handleCallback;
    }

    /**
     * @inheritdoc
     */
    public function handleExecutionError(ExecutionException $exception, ExecutionContext $context, callable $next)
    {
        \call_user_func($this->handleCallback, $exception, $context, $next);
    }
}
