<?php

namespace Digia\GraphQL\Schema\Resolver;

interface ResolverInterface
{
    /**
     * @return callable|null
     */
    public function getResolveCallback(): ?callable;

    /**
     * @return callable|null
     */
    public function getTypeResolver(): ?callable;
}
