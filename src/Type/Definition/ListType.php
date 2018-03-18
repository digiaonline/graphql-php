<?php

namespace Digia\GraphQL\Type\Definition;

class ListType implements TypeInterface, WrappingTypeInterface
{
    use NameTrait;
    use DescriptionTrait;
    use OfTypeTrait;

    /**
     * ListType constructor.
     * @param TypeInterface $ofType
     */
    public function __construct(TypeInterface $ofType)
    {
        $this->setOfType($ofType);
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return '[' . (string)$this->getOfType() . ']';
    }
}
