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
     * @return callable|null
     */
    public function getResolve(): ?callable
    {
        return $this->_resolveFunction;
    }

    /**
     * @return bool
     */
    public function hasResolve()
    {
        return $this->_resolveFunction !== null;
    }

    /**
     * Because of the use of ConfigTrait, setter name must match with attribute in $config array
     *
     * @param callable|null $resolveFunction
     * @return $this
     */
    protected function setResolve(?callable $resolveFunction)
    {
        $this->_resolveFunction = $resolveFunction;
        return $this;
    }
}
