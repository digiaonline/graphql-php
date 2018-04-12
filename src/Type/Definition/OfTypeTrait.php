<?php

namespace Digia\GraphQL\Type\Definition;

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
