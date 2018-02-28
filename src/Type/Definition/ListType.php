<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Type\Definition\DescriptionTrait;
use Digia\GraphQL\Type\Definition\NameTrait;
use Digia\GraphQL\Type\Definition\OfTypeTrait;
use Digia\GraphQL\Type\Definition\TypeInterface;
use Digia\GraphQL\Type\Definition\WrappingTypeInterface;

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

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return '[' . $this->getOfType() . ']';
    }
}
