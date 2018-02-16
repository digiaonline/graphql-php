<?php

namespace Digia\GraphQL\Type\Definition\Contract;

interface TypeInterface
{

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @return string
     */
    public function __toString(): string;
}
