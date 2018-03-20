<?php

namespace Digia\GraphQL\Type\Definition;

trait ResolveTrait
{

    /**
     * @var callable|null
     */
    protected $resolveFunction;

    /**
     * @param array ...$args
     * @return mixed
     */
    public function resolve(...$args)
    {
        return isset($this->resolveFunction)
            ? \call_user_func_array($this->resolveFunction, $args)
            : null;
    }

    /**
     * @return bool
     */
    public function hasResolve()
    {
        return null !== $this->resolveFunction;
    }

    /**
     * @return callable|null
     */
    public function getResolve(): ?callable
    {
        return $this->resolveFunction;
    }

    /**
     * Classes that use the `ResolveTrait` are created using the `ConfigAwareTrait` constructor which will automatically
     * call this method when setting arguments from `$config['resolve']`.
     *
     * @param callable|null $resolveFunction
     * @return $this
     */
    protected function setResolve(?callable $resolveFunction)
    {
        $this->resolveFunction = $resolveFunction;
        return $this;
    }
}
