<?php

namespace Digia\GraphQL\Type\Definition;

trait ResolveTypeTrait
{

    /**
     * @var callable|null
     */
    protected $resolveTypeFunction;

    /**
     * @param array ...$args
     * @return TypeInterface|string|null
     */
    public function resolveType(...$args)
    {
        return isset($this->resolveTypeFunction)
            ? \call_user_func_array($this->resolveTypeFunction, $args)
            : null;
    }

    /**
     * Classes that use the `ResolveType Trait` are created using the `ConfigAwareTrait` constructor which will
     * automatically call this method when setting arguments from `$config['resolveType']`.
     *
     * @param callable|null $resolveTypeFunction
     * @return $this
     */
    protected function setResolveType(?callable $resolveTypeFunction)
    {
        $this->resolveTypeFunction = $resolveTypeFunction;
        return $this;
    }
}
