<?php

namespace Digia\GraphQL\Type\Definition;

trait ResolveTrait
{

    /**
     * @var ?callable
     */
    private $_resolveFunction;

    /**
     * @param array ...$args
     * @return mixed
     */
    public function resolve(...$args)
    {
        return $this->_resolveFunction !== null ? \call_user_func_array($this->_resolveFunction, $args) : null;
    }

    /**
     * @param callable $resolveFunction
     * @return $this
     */
    protected function setResolve(callable $resolveFunction)
    {
        $this->_resolveFunction = $resolveFunction;
        return $this;
    }
}
