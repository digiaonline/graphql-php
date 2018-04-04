<?php

namespace Digia\GraphQL\Schema\Resolver;

class AbstractResolver implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function getResolveMethod(string $fieldName): ?callable
    {
        $resolveMethod = 'resolve' . \ucfirst($fieldName);

        if (\method_exists($this, $resolveMethod)) {
            return [$this, $resolveMethod];
        }

        return null;
    }
}
