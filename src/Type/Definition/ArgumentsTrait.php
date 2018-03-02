<?php

namespace Digia\GraphQL\Type\Definition;

use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Util\invariant;

trait ArgumentsTrait
{

    /**
     * @var Argument[]
     */
    private $_arguments = [];

    /**
     * @return bool
     */
    public function hasArguments(): bool
    {
        return !empty($this->_arguments);
    }

    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->_arguments;
    }

    /**
     * @param Argument[] $arguments
     * @return $this
     * @throws \Exception
     */
    protected function setArgs(array $arguments)
    {
        invariant(
            isAssocArray($arguments),
            'Args must be an associative array with argument names as keys.'
        );

        foreach ($arguments as $argName => $argConfig) {
            $this->_arguments[] = new Argument(array_merge($argConfig, ['name' => $argName]));
        }

        return $this;
    }
}
