<?php

namespace Digia\GraphQL\Type\Definition;

class NonNullType implements TypeInterface, WrappingTypeInterface
{
    use NameTrait;
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
