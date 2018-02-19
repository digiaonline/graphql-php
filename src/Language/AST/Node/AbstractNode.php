<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\ConfigObject;

abstract class AbstractNode extends ConfigObject
{

    /**
     * @var string
     */
    protected $kind;

    /**
     * @var ?Location
     */
    protected $location;

    /**
     * @return string
     */
    public function getKind(): string
    {
        return $this->kind;
    }

    /**
     * @return mixed
     */
    public function getLocation()
    {
        return $this->location;
    }
}
