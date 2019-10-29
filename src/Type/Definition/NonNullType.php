<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Schema\Definition;

class NonNullType extends Definition implements
    TypeInterface,
    WrappingTypeInterface
{
    use OfTypeTrait;

    /**
     * NonNullType constructor.
     *
     * @param TypeInterface $ofType
     */
    public function __construct(TypeInterface $ofType)
    {
        $this->ofType = $ofType;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return (string)$this->ofType . '!';
    }
}
