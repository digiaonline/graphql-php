<?php

namespace Digia\GraphQL\Error;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handleError(ExecutionException $exception)
    {
        // The default error handler does not need to do anything.
    }
}
