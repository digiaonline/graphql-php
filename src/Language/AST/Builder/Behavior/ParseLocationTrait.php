<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\Location;

trait ParseLocationTrait
{

    /**
     * @param array $ast
     * @return Location
     */
    protected function parseLocation(array $ast): Location
    {
        return isset($ast['loc']) ? $this->createLocation($ast['loc']) : null;
    }

    /**
     * @param array $ast
     * @return Location
     */
    protected function createLocation(array $ast): Location
    {
        return new Location($ast['startToken'], $ast['endToken']);
    }
}
