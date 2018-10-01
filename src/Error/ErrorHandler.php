<?php

namespace Digia\GraphQL\Error;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handleError(ExecutionException $exception)
    {
        // Default error handler does not need to do anything.
    }
}
