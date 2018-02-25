<?php

namespace Digia\GraphQL\Language\AST\Node\Contract;

interface ValueNodeInterface extends NodeInterface
{
    /**
     * @return mixed
     */
    public function getValue();
}
