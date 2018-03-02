<?php

namespace Digia\GraphQL\Util;

interface SerializationInterface
{

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return string
     */
    public function toJSON(): string;
}
