<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Contract\SerializationInterface;
use Digia\GraphQL\Language\AST\Node\Contract\TypeNodeInterface;

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
