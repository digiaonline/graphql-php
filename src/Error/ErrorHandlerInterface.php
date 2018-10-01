<?php

namespace Digia\GraphQL\Error;

interface ErrorHandlerInterface
{
    /**
     * @param ExecutionException $error
     */
    public function handleError(ExecutionException $error);
}
