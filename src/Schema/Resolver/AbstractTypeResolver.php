<?php

namespace Digia\GraphQL\Schema\Resolver;

abstract class AbstractTypeResolver implements ResolverCollectionInterface
{
    use ResolverTrait;

    /**
     * @return callable|null
     */
    public function getResolveCallback(): ?callable
    {
        return function (string $fieldName) {
            return $this->getResolver($fieldName);
        };
    }

    /**
     * @param string $fieldName
     * @return callable|null
     */
    public function getResolver(string $fieldName): ?callable
    {
        $resolveMethod = 'resolve' . \ucfirst($fieldName);

        if (\method_exists($this, $resolveMethod)) {
            return [$this, $resolveMethod];
        }

        return null;
    }
}
