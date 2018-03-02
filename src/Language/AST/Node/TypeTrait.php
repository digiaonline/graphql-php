<?php

namespace Digia\GraphQL\Language\AST\Node;

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
}
