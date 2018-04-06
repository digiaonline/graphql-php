<?php

namespace Digia\GraphQL\Type\Definition;

interface ArgumentsAwareInterface
{

    /**
     * @return bool
     */
    public function hasArguments(): bool;

    /**
     * @return Argument[]
     */
    public function getArguments(): array;
}
