<?php

namespace Digia\GraphQL\Type\Definition;

trait ArgumentsTrait
{

    /**
     * @var Argument[]
     */
    private $arguments = [];

    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

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
        $this->arguments = array_map(function ($config) {
            $this->addArgument(new Argument($config));
        }, $arguments);
    }
}
