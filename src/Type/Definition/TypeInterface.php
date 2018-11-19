<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Schema\DefinitionInterface;

/**
 * Tagging interface for GraphQL types.
 */
interface TypeInterface extends DefinitionInterface
{
    /**
     * @return string
     */
    public function __toString(): string;
}
