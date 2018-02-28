<?php

namespace Digia\GraphQL\Language\AST\Node;

trait ValueTrait
{

    /**
     * @var mixed|null
     */
    protected $value;

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
