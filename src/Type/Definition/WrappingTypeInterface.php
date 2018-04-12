<?php

namespace Digia\GraphQL\Type\Definition;

/**
 * Tagging interface for wrapping types.
 */
interface WrappingTypeInterface extends TypeInterface
{
    /**
     * @return TypeInterface
     */
    public function getOfType(): TypeInterface;
}
