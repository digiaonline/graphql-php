<?php

namespace Digia\GraphQL\Type\Definition;

trait ResolveTypeTrait
{

    /**
     * @var ?callable
     */
    private $resolveTypeFunction;

    /**
     * @param array ...$args
     * @return TypeInterface|string|null
     */
    public function resolveType(...$args)
    {
        if(isset($this->resolveTypeFunction)) {
            return \call_user_func_array($this->resolveTypeFunction, $args);
        }

        return null;
    }

    /**
     * @param callable|null $resolveTypeFunction
     * @return $this
     */
    protected function setResolveType(?callable $resolveTypeFunction)
    {
        $this->resolveTypeFunction = $resolveTypeFunction;

        return $this;
    }
}
