<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Schema\Definition;
use GraphQL\Contracts\TypeSystem\Type\ListTypeInterface;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;

class ListType extends Definition implements ListTypeInterface
{
    use OfTypeTrait;

    /**
     * ListType constructor.
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
        return '[' . (string)$this->ofType . ']';
    }
}
