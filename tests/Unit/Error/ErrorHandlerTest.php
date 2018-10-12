<?php

namespace Digia\GraphQL\Test\Unit\Error;

use Digia\GraphQL\Error\ErrorHandler;
use Digia\GraphQL\Execution\ExecutionException;
use Digia\GraphQL\Test\TestCase;

class ErrorHandlerTest extends TestCase
{
    public function testHandleError()
    {
        $wasCallbackInvoked = false;

        $handleCallback = function (ExecutionException $exception) use (&$wasCallbackInvoked) {
            $wasCallbackInvoked = true;
        };

        $errorHandler = new ErrorHandler($handleCallback);

        $errorHandler->handleError(new ExecutionException('This is an exception.'));

        $this->assertTrue($wasCallbackInvoked);
    }
}
