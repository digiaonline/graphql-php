<?php

namespace Digia\GraphQL\Type\Definition;

use GraphQL\Contracts\TypeSystem\Type\TypeInterface;

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
