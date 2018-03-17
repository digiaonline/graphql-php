<?php

namespace Digia\GraphQL\Util;

trait ArrayToJsonTrait
{
    /**
     * @return array
     */
    abstract public function toArray(): array;

    /**
     * @return string
     */
    public function toJSON(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
