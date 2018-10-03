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
     * @param GraphQLException $exception
     */
    public function handleError(GraphQLException $exception)
    {
        \call_user_func($this->handleCallback, $exception);
    }
}
