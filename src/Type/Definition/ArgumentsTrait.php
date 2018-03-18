<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Util\invariant;

trait ArgumentsTrait
{
    /**
     * @var Argument[]
     */
    protected $arguments = [];

    /**
     * @return bool
     */
    public function hasArguments(): bool
    {
        return !empty($this->arguments);
    }

    /**
     * @return Argument[]
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Arguments are created using the `ConfigAwareTrait` constructor which will automatically
     * call this method when setting arguments from `$config['args']`.
     *
     * @param Argument[] $arguments
     * @return $this
     * @throws InvariantException
     */
    protected function setArgs(array $arguments)
    {
        invariant(
            isAssocArray($arguments),
            'Args must be an associative array with argument names as keys.'
        );

        foreach ($arguments as $argumentName => $argumentConfig) {
            $argumentConfig['name'] = $argumentName;
            $this->arguments[] = new Argument($argumentConfig);
        }

        return $this;
    }
}
