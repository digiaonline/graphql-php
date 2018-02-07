<?php

namespace Digia\GraphQL\Type\Definition;

use function Digia\GraphQL\Util\instantiateIfNecessary;

trait ArgumentsTrait
{

    /**
     * @var Argument[]
     */
    private $arguments = [];

    /**
     * @param Argument $argument
     */
    protected function addArgument(Argument $argument): void
    {
        $this->arguments[] = $argument;
    }

    /**
     * @param Argument[] $arguments
     */
    protected function setArguments(array $arguments): void
    {
        $this->arguments = array_map(function ($argument) {
            $this->addArgument(instantiateIfNecessary(Argument::class, $argument));
        }, $arguments);
    }
}
