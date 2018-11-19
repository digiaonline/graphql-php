<?php

namespace Digia\GraphQL\Error\Handler;

use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ExecutionException;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var ErrorMiddlewareInterface[]
     */
    protected $middleware = [];

    /**
     * ErrorHandler constructor.
     * @param ErrorMiddlewareInterface[] $middleware
     */
    public function __construct(array $middleware)
    {
        foreach ($middleware as $mw) {
            $this->addMiddleware($mw);
        }
    }

    /**
     * @param \Throwable $exception
     */
    public function handleError(\Throwable $exception): void
    {
        $next = function (...$args) {
            // NO-OP
        };

        foreach ($this->middleware as $middleware) {
            $next = function (\Throwable $exception) use ($middleware, $next) {
                return $middleware->handleError($exception, $next);
            };
        }

        $next($exception);
    }

    /**
     * @inheritdoc
     */
    public function handleExecutionError(ExecutionException $exception, ExecutionContext $context): void
    {
        $next = function (...$args) {
            // NO-OP
        };

        foreach ($this->middleware as $middleware) {
            $next = function (ExecutionException $exception, ExecutionContext $context) use ($middleware, $next) {
                return $middleware->handleExecutionError($exception, $context, $next);
            };
        }

        $next($exception, $context);
    }

    /**
     * @param ErrorMiddlewareInterface $middleware
     */
    protected function addMiddleware(ErrorMiddlewareInterface $middleware): void
    {
        \array_unshift($this->middleware, $middleware);
    }
}
