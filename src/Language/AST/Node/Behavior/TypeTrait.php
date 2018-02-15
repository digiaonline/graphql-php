<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\Contract\TypeNodeInterface;

trait TypeTrait
{

    /**
     * @var TypeNodeInterface
     */
    protected $type;

    /**
     * @return TypeNodeInterface
     */
    public function getType(): TypeNodeInterface
    {
        return $this->type;
    }
}
