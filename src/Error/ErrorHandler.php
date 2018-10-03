<?php

namespace Digia\GraphQL\Error;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var callable
     */
    protected $handleCallback;

    /**
     * CallbackErrorHandler constructor.
     * @param callable $handleCallback
     */
    public function __construct(callable $handleCallback)
    {
        $this->handleCallback = $handleCallback;
    }

    /**
     * @param ExecutionException $exception
     */
    public function handleError(ExecutionException $exception)
    {
        \call_user_func($this->handleCallback, $exception);
    }
}
