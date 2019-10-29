<?php

namespace Digia\GraphQL\Type\Definition;

use GraphQL\Contracts\TypeSystem\Type\TypeInterface;

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
