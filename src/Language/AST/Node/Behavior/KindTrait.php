<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

trait KindTrait
{

    /**
     * @var string
     */
    protected $kind;

    /**
     * @return string
     */
    public function getKind(): string
    {
        return $this->kind;
    }
}
