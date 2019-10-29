<?php

namespace Digia\GraphQL\Type\Definition;

use GraphQL\Contracts\TypeSystem\Type\TypeInterface;

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
     * @return bool
     */
    public function hasResolveTypeCallback(): bool
    {
        return null !== $this->resolveTypeCallback;
    }

    /**
     * @return callable|null
     */
    public function getResolveTypeCallback(): ?callable
    {
        return $this->resolveTypeCallback;
    }
}
