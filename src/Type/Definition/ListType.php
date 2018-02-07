<?php

namespace Digia\GraphQL\Type\Definition;

class ListType implements TypeInterface, WrappingTypeInterface
{

    use NameTrait;
    use DescriptionTrait;
    use OfTypeTrait;

    /**
     * ListType constructor.
     *
     * @param TypeInterface $ofType
     * @throws \TypeError
     */
    public function __construct(TypeInterface $ofType)
    {
        $this->setOfType($ofType);
    }
}
