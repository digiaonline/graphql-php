<?php

namespace Digia\GraphQL\Language\AST\Node\Contract;

use Digia\GraphQL\Language\Location;

interface NodeInterface
{

    /**
     * @return string
     */
    public function getKind(): string;

    /**
     * @return Location|null
     */
    public function getLocation(): ?Location;

    /**
     * @return string
     */
    public function toJSON(): string;
}
