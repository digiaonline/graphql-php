<?php

namespace Digia\GraphQL\Type\Definition;

trait ResolveTrait
{
    /**
     * @var callable|null
     */
    protected $resolveCallback;

    /**
     * @param array ...$args
     * @return mixed
     */
    public function resolve(...$args)
    {
        return null !== $this->resolveCallback
            ? \call_user_func_array($this->resolveCallback, $args)
            : null;
    }

    /**
     * @return bool
     */
    public function hasResolveCallback()
    {
        return null !== $this->resolveCallback;
    }

    /**
     * @return callable|null
     */
    public function getResolveCallback(): ?callable
    {
        return $this->resolveCallback;
    }
}
