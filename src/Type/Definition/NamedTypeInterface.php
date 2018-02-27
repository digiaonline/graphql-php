<?php

namespace Digia\GraphQL\Type\Definition;

interface NamedTypeInterface
{

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function __toString(): string;
}
