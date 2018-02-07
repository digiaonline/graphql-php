<?php

namespace Digia\GraphQL\Type\Definition;

trait ResolveTrait
{

    /**
     * @var callable
     */
    private $resolve;

    /**
     * @param callable $resolve
     */
    protected function setResolve(callable $resolve): void
    {
        $this->resolve = $resolve;
    }
}
