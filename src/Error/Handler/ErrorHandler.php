<?php

namespace Digia\GraphQL\Error\Handler;

use Digia\GraphQL\Execution\ExecutionContext;
use Digia\GraphQL\Execution\ExecutionException;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var ErrorMiddlewareInterface[]
     */
    protected $middleware;

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
     * @inheritdoc
     */
    public function handle(ExecutionException $exception, ExecutionContext $context)
    {
        $next = function () {
            // NO-OP
        };

        foreach (\array_reverse($this->middleware) as $mw) {
            /** @var ErrorMiddlewareInterface $mw */
            $next = function (ExecutionException $exception, ExecutionContext $context) use ($mw, $next) {
                return $mw->handle($exception, $context, $next);
            };
        }

        \call_user_func($next, $exception, $context);
    }

    /**
     * @param ErrorMiddlewareInterface $middleware
     */
    protected function addMiddleware(ErrorMiddlewareInterface $middleware)
    {
        $this->middleware[] = $middleware;
    }
}
