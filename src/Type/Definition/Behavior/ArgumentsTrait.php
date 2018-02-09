<?php

namespace Digia\GraphQL\Type\Definition\Behavior;

use Digia\GraphQL\Type\Definition\Argument;
use function Digia\GraphQL\Util\instantiateAssocFromArray;

trait ArgumentsTrait
{

    /**
     * @var Argument[]
     */
    private $args = [];

    /**
     * @return bool
     */
    public function hasArgs(): bool
    {
        return !empty($this->args);
    }

    /**
     * @return Argument[]
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param Argument $arg
     * @return $this
     */
    protected function addArgument(Argument $arg)
    {
        $this->args[] = $arg;

        return $this;
    }

    /**
     * @param array $args
     * @return $this
     */
    protected function addArguments(array $args)
    {
        foreach ($args as $argument) {
            $this->addArgument($argument);
        }

        return $this;
    }

    /**
     * @param Argument[] $args
     * @return $this
     */
    protected function setArgs(array $args)
    {
        $this->addArguments(instantiateAssocFromArray(Argument::class, $args));

        return $this;
    }
}
