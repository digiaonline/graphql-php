<?php

namespace Digia\GraphQL\Type\Definition;

interface SerializeInterface
{

    /**
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value);
}
