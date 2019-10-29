<?php

namespace Digia\GraphQL\Schema;

use GraphQL\Contracts\TypeSystem\DefinitionInterface;

abstract class Definition implements DefinitionInterface
{
    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return \get_object_vars($this);
    }
}