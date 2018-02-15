<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

trait ValueTrait
{

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
