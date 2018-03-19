<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvalidTypeException;

class NonNullType implements TypeInterface, WrappingTypeInterface
{
    use NameTrait;
    use DescriptionTrait;
    use OfTypeTrait;

    /**
     * NonNullType constructor.
     * @param TypeInterface $ofType
     * @throws InvalidTypeException
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
        return (string)$this->getOfType() . '!';
    }

    /**
     * @param TypeInterface $ofType
     * @return $this
     * @throws InvalidTypeException
     */
    public function setOfType(TypeInterface $ofType)
    {
        if ($ofType instanceof NonNullType) {
            throw new InvalidTypeException(\sprintf('Expected %s to be a GraphQL nullable type.', (string)$ofType));
        }

        $this->ofType = $ofType;

        return $this;
    }
}
