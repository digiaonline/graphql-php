<?php

namespace Digia\GraphQL\Type\Definition;

trait TypeTrait
{
    /**
     * @var TypeInterface|null
     */
    protected $type;

    /**
     * @return TypeInterface|null
     */
    public function getType(): ?TypeInterface
    {
        return $this->type;
    }
}
