<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Type\Definition\Argument;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Util\invariant;

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
     * @throws \Exception
     */
    protected function setArgs(array $args)
    {
        invariant(
            isAssocArray($args),
            'Args must be an associative array with argument names as keys.'
        );

        foreach ($args as $argName => $argConfig) {
            $this->addArgument(new Argument(array_merge($argConfig, ['name' => $argName])));
        }

        return $this;
    }
}
