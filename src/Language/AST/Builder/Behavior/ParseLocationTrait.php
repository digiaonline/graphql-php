<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\NodeFactoryInterface;
use Digia\GraphQL\Language\Location;

trait ParseLocationTrait
{

    /**
     * @return NodeFactoryInterface
     */
    abstract protected function getFactory(): NodeFactoryInterface;

    /**
     * @param array $ast
     * @return Location
     */
    protected function parseLocation(array $ast): Location
    {
        return isset($ast['loc']) ? $this->getFactory()->createLocation($ast['loc']) : null;
    }
}
