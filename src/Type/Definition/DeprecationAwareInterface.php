<?php

namespace Digia\GraphQL\Type\Definition;

/**
 * Interface for GraphQL definitions that can be deprecated.
 */
interface DeprecationAwareInterface
{
    /**
     * @return null|string
     */
    public function getDeprecationReason(): ?string;

    /**
     * @return bool
     */
    public function isDeprecated(): bool;
}
