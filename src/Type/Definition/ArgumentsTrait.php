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
     * @param Argument[] $arguments
     * @return $this
     */
    protected function setArguments(array $arguments)
    {
        foreach ($arguments as $config) {
            $this->addArgument(new Argument($config));
        }

        return $this;
    }
}
