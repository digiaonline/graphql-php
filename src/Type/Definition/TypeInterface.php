<?php

namespace Digia\GraphQL\Type\Definition;

interface TypeInterface
{

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @return string
     */
    public function __toString(): string;
}
