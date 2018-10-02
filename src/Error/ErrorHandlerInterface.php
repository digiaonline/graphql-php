<?php

namespace Digia\GraphQL\Error;

interface ErrorHandlerInterface
{
    /**
     * @param ExecutionException $exception
     */
    public function handleError(ExecutionException $exception);
}
