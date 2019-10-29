<?php

namespace Digia\GraphQL\Type\Definition;

use GraphQL\Contracts\TypeSystem\Type\TypeInterface;
use GraphQL\Contracts\TypeSystem\Type\WrappingTypeInterface;

/**
 * @mixin WrappingTypeInterface
 */
trait OfTypeTrait
{
    /**
     * @var TypeInterface
     */
    protected $ofType;

    /**
     * @return TypeInterface
     */
    public function getOfType(): TypeInterface
    {
        return $this->ofType;
    }
}
