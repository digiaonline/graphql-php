<?php

namespace Digia\GraphQL\Error;

use Digia\GraphQL\Execution\ExecutionException;

interface ErrorHandlerInterface
{
    /**
     * @param ExecutionException $exception
     */
    public function handleError(ExecutionException $exception);
}
