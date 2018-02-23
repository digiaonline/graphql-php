<?php

namespace Digia\GraphQL\Contract;

interface SerializationInterface
{

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return string
     */
//    public function toJSON(): string;
}
