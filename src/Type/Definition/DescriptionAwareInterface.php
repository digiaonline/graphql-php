<?php

namespace Digia\GraphQL\Type\Definition;

interface DescriptionAwareInterface
{
    /**
     * @return null|string
     */
    public function getDescription(): ?string;
}
