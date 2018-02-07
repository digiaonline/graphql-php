<?php

namespace Digia\GraphQL\Type\Definition;

trait ResolveTypeTrait
{

    /**
     * @var ?callable
     */
    private $resolveType;

    /**
     * @return callable|null
     */
    public function getResolveType(): ?callable
    {
        return $this->resolveType;
    }

    /**
     * @param callable|null $resolveType
     * @return $this
     */
    protected function setResolveType(?callable $resolveType)
    {
        $this->resolveType = $resolveType;

        return $this;
    }
}
