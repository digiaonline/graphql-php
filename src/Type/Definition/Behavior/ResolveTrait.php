<?php

namespace Digia\GraphQL\Type\Definition\Behavior;

trait ResolveTrait
{

    /**
     * @var ?callable
     */
    protected $resolveFunction;

    /**
     * @param array ...$args
     * @return mixed
     */
    public function resolve(...$args)
    {
        return $this->resolveFunction !== null ? call_user_func_array($this->resolveFunction, $args) : null;
    }

    /**
     * @param callable $resolveFunction
     * @return $this
     */
    public function setResolve(callable $resolveFunction)
    {
        $this->resolveFunction = $resolveFunction;
        return $this;
    }
}
