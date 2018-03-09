<?php

namespace Digia\GraphQL\Type\Definition;

trait OfTypeTrait
{

    /**
     * @var TypeInterface
     */
    private $ofType;

    /**
     * @return mixed
     */
    public function getOfType(): TypeInterface
    {
        return $this->ofType;
    }

    /**
     * @param TypeInterface $ofType
     * @return $this
     */
    protected function setOfType(TypeInterface $ofType)
    {
        $this->ofType = $ofType;

        return $this;
    }
}
