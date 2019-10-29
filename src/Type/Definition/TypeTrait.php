<?php

namespace Digia\GraphQL\Type\Definition;

use GraphQL\Contracts\TypeSystem\Common\TypeAwareInterface;
use GraphQL\Contracts\TypeSystem\Type\TypeInterface;

/**
 * @mixin TypeAwareInterface
 */
trait TypeTrait
{
    /**
     * @var TypeInterface|null
     */
    protected $type;

    /**
     * @return TypeInterface
     */
    public function getType(): TypeInterface
    {
        return $this->type;
    }

    /**
     * @internal This is a method for obtaining a type that implements compatibility with an current implementation
     * @return TypeInterface|null
     */
    public function getNullableType(): ?TypeInterface
    {
        return $this->type;
    }
}
