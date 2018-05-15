<?php

namespace Digia\GraphQL\Language\Node;

interface ValueAwareInterface
{
    /**
     * @return mixed|null
     */
    public function getValue();
}
