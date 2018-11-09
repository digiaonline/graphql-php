<?php

namespace Digia\GraphQL\Test\Unit\Error\Handler;

use Digia\GraphQL\Error\Handler\AbstractErrorMiddleware;
use Digia\GraphQL\Error\Handler\ErrorHandler;
use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ExecutionException;
use Digia\GraphQL\Test\TestCase;

class ErrorHandlerTest extends TestCase
{
    public function testHandle()
    {
        $middleware   = new WasInvokedMiddleware();
        $errorHandler = new ErrorHandler([$middleware]);
        $exception    = new ExecutionException('This is an exception.');
        $context      = $this->mockContext();

        $errorHandler->handleExecutionError($exception, $context);

        $this->assertTrue($middleware->wasInvoked());
    }

    public function testMiddleware()
    {
        $result = [];

        $middlewareA    = new LoggerMiddleware(function () use (&$result) {
            $result[] = 'Middleware A invoked';
        });
        $middlewareB    = new LoggerMiddleware(function () use (&$result) {
            $result[] = 'Middleware B invoked';
        });
        $middlewareC    = new LoggerMiddleware(function () use (&$result) {
            $result[] = 'Middleware C invoked';
        });
        $noopMiddleware = new NoopMiddleware();
        $errorHandler   = new ErrorHandler([$middlewareA, $middlewareB, $noopMiddleware, $middlewareC]);
        $exception      = new ExecutionException('This is an exception.');
        $context        = $this->mockContext();

        $errorHandler->handleExecutionError($exception, $context);

        $this->assertSame([
            'Middleware A invoked',
            'Middleware B invoked',
        ], $result);
    }

    /**
     * @return ExecutionContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockContext()
    {
        return $this->getMockBuilder(ExecutionContext::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}

class WasInvokedMiddleware extends AbstractErrorMiddleware
{
    private $wasInvoked = false;

    public function handleExecutionError(ExecutionException $exception, ExecutionContext $context, callable $next)
    {
        $this->wasInvoked = true;

        return $next($exception, $context);
    }

    public function wasInvoked(): bool
    {
        return $this->wasInvoked;
    }
}

class LoggerMiddleware extends AbstractErrorMiddleware
{
    private $logCallback;

    public function __construct($logCallback)
    {
        $this->logCallback = $logCallback;
    }

    public function handleExecutionError(ExecutionException $exception, ExecutionContext $context, callable $next)
    {
        \call_user_func($this->logCallback, $exception, $context);

        return $next($exception, $context);
    }
}

class NoopMiddleware extends AbstractErrorMiddleware
{
}
