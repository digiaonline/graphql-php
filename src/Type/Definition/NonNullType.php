<?php

namespace Digia\GraphQL\Type\Definition;

class NonNullType implements WrappingTypeInterface
{

    use OfTypeTrait;

    /**
     * NonNullType constructor.
     *
     * @param TypeInterface $ofType
     * @throws \TypeError
     */
    public function __construct(TypeInterface $ofType)
    {
        $this->setOfType($ofType);
    }
}
