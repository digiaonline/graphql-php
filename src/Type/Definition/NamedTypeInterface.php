<?php

namespace Digia\GraphQL\Type\Definition;

/**
 * Interface for all named types (everything except List and NonNull).
 */
interface NamedTypeInterface extends TypeInterface
{
    /**
     * @return string
     */
    public function getName(): string;
}
