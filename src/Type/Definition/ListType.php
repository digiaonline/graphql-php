<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Behavior\DescriptionTrait;
use Digia\GraphQL\Type\Behavior\NameTrait;
use Digia\GraphQL\Type\Behavior\OfTypeTrait;
use Digia\GraphQL\Type\Contract\TypeInterface;
use Digia\GraphQL\Type\Contract\WrappingTypeInterface;

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
