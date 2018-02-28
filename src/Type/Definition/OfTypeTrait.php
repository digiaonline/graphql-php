<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Type\Definition\TypeInterface;

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
     * @throws \TypeError
     */
    protected function setOfType(TypeInterface $ofType)
    {
        $this->ofType = $ofType;

        return $this;
    }
}
