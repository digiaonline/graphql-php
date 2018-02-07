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
     */
    protected function setResolveType(?callable $resolveType): void
    {
        $this->resolveType = $resolveType;
    }
}
