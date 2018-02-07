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
     * @throws \TypeError
     */
    protected function setOfType(TypeInterface $ofType): void
    {
        if ($ofType instanceof NonNullType) {
            throw new \TypeError('NonNull cannot be of type NonNull');
        }

        $this->ofType = $ofType;
    }
}
