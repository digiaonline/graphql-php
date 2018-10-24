<?php

namespace Digia\GraphQL\Error\Handler;

use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ExecutionException;

interface ErrorHandlerInterface
{
    /**
     * @param ExecutionException $exception
     * @param ExecutionContext   $context
     */
    public function handle(ExecutionException $exception, ExecutionContext $context);
}
