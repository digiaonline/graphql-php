<?php

namespace Digia\GraphQL\Type\Definition;

interface NamedTypeInterface
{
    /**
     * @return string|null
     */
    public function getName(): ?string;
}
