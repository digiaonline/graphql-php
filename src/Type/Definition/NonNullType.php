<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Type\Definition\Behavior\DescriptionTrait;
use Digia\GraphQL\Type\Definition\Behavior\NameTrait;
use Digia\GraphQL\Type\Definition\Behavior\OfTypeTrait;
use Digia\GraphQL\Type\Definition\Contract\TypeInterface;
use Digia\GraphQL\Type\Definition\Contract\WrappingTypeInterface;

class NonNullType implements TypeInterface, WrappingTypeInterface
{

    use NameTrait;
    use DescriptionTrait;
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

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->getOfType() . '!';
    }

    /**
     * @param TypeInterface $ofType
     * @return $this
     * @throws \TypeError
     */
    protected function setOfType(TypeInterface $ofType)
    {
        if ($ofType instanceof NonNullType) {
            throw new \TypeError(sprintf('Expected %s to be a GraphQL nullable type.', $ofType));
        }

        $this->ofType = $ofType;

        return $this;
    }
}
