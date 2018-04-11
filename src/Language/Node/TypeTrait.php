<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Util\SerializationInterface;

trait TypeTrait
{
    /**
     * @var TypeNodeInterface|SerializationInterface
     */
    protected $type;

    /**
     * @return TypeNodeInterface
     */
    public function getType(): TypeNodeInterface
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getTypeAsArray(): array
    {
        return $this->type->toArray();
    }

    /**
     * @param TypeNodeInterface $type
     * @return $this
     */
    public function setType(TypeNodeInterface $type)
    {
        $this->type = $type;
        return $this;
    }
}
