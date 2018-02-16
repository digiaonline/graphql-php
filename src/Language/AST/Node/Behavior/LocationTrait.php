<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Location;

trait LocationTrait
{

    /**
     * @var ?Location
     */
    protected $loc;

    /**
     * @return mixed
     */
    public function getLoc(): ?Location
    {
        return $this->loc;
    }
}
