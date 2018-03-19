<?php

namespace Digia\GraphQL\Type\Definition;

interface NamedTypeInterface
{
    /**
     * @return string
     */
    public function getName(): string;
}
