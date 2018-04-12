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
     * @param array|Argument[] $arguments
     * @return $this
     * @throws InvariantException
     */
    protected function buildArguments(array $arguments)
    {
        invariant(
            isAssocArray($arguments),
            'Args must be an associative array with argument names as keys.'
        );

        foreach ($arguments as $argumentName => $argument) {
            $this->arguments[] = $argument instanceof Argument
                ? $argument
                : new Argument(
                    $argumentName,
                    $argument['type'] ?? null,
                    $argument['defaultValue'] ?? null,
                    $argument['description'] ?? null,
                    $argument['astNode'] ?? null
                );
        }

        return $this;
    }
}
