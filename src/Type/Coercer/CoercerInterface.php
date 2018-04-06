<?php

namespace Digia\GraphQL\Type\Coercer;

interface CoercerInterface
{

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function coerce($value);
}
