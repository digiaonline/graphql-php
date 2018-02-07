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
     * @return $this
     */
    protected function addArgument(Argument $argument)
    {
        $this->arguments[] = $argument;

        return $this;
    }

    /**
     * @param array $arguments
     * @return $this
     */
    protected function addArguments(array $arguments)
    {
        foreach ($arguments as $argument) {
            $this->addArgument($argument);
        }

        return $this;
    }

    /**
     * @param Argument[] $arguments
     * @return $this
     */
    protected function setArguments(array $arguments)
    {
        $this->addArguments(array_map(function ($config) {
            return new Argument($config);
        }, $arguments));

        return $this;
    }
}
