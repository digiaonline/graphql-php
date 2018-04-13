<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\newArgument;
use function Digia\GraphQL\Util\invariant;

trait ArgumentsTrait
{
    /**
     * @var array
     */
    protected $rawArguments = [];

    /**
     * @var Argument[]
     */
    protected $arguments;

    /**
     * @return null|string
     */
    public abstract function getName(): ?string;

    /**
     * @return array
     */
    public function getRawArguments(): array
    {
        return $this->rawArguments;
    }

    /**
     * @return bool
     */
    public function hasArguments(): bool
    {
        return !empty($this->arguments);
    }

    /**
     * @return Argument[]
     * @throws InvariantException
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * @param string $typeName
     * @param array  $rawArguments
     * @return Argument[]
     * @throws InvariantException
     */
    protected function buildArguments(string $typeName, array $rawArguments): array
    {
        invariant(
            isAssocArray($rawArguments),
            \sprintf(
                '%s.%s args must be an object with argument names as keys.',
                $typeName,
                $this->getName()
            )
        );

        $arguments = [];

        foreach ($rawArguments as $argumentName => $argumentConfig) {
            $argumentConfig['name'] = $argumentName;

            $arguments[] = newArgument($argumentConfig);
        }

        return $arguments;
    }
}
