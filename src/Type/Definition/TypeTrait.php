<?php

namespace Digia\GraphQL\Type\Definition;

trait TypeTrait
{

    /**
     * @var TypeInterface
     */
    private $type;

    /**
     * @return TypeInterface
     */
    public function getType(): TypeInterface
    {
        return $this->type;
    }

    /**
     * @param TypeInterface $type
     */
    protected function setType(TypeInterface $type): void
    {
        $this->type = $type;
    }
}
