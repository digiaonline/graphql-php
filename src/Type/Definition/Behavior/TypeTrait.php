<?php

namespace Digia\GraphQL\Type\Definition\Behavior;

use Digia\GraphQL\Type\Definition\Contract\TypeInterface;

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
     * @return $this
     */
    protected function setType(TypeInterface $type)
    {
        $this->type = $type;

        return $this;
    }
}
