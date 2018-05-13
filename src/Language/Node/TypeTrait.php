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
     * @return TypeNodeInterface|SerializationInterface
     */
    public function getType()
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
     * @param TypeNodeInterface|SerializationInterface $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
