<?php

namespace Digia\GraphQL\Type\Definition;

trait ResolveTypeTrait
{

    /**
     * @var callable|null
     */
    protected $resolveTypeCallback;

    /**
     * @param array ...$args
     * @return TypeInterface|string|null
     */
    public function resolveType(...$args)
    {
        return null !== $this->resolveTypeCallback
            ? \call_user_func_array($this->resolveTypeCallback, $args)
            : null;
    }

    /**
     * @return callable|null
     */
    public function getResolveType(): ?callable
    {
        return $this->resolveTypeCallback;
    }

    /**
     * @param callable|null $resolveTypeFunction
     * @return $this
     */
    protected function setResolveType(?callable $resolveTypeFunction)
    {
        $this->resolveTypeCallback = $resolveTypeFunction;
        return $this;
    }
}
