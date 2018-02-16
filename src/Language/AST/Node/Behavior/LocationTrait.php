<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Location;

trait LocationTrait
{

    /**
     * @var ?Location
     */
    protected $location;

    /**
     * @return mixed
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }
}
