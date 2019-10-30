<?php

namespace Digia\GraphQL\Type\Definition;

use Digia\GraphQL\Error\InvariantException;
use GraphQL\Contracts\TypeSystem\ArgumentInterface;
use function Digia\GraphQL\Type\isAssocArray;
use function Digia\GraphQL\Type\newArgument;
use GraphQL\Contracts\TypeSystem\Common\ArgumentsAwareInterface as ArgumentsAwareContract;

/**
 * @mixin ArgumentsAwareContract
 */
trait ArgumentsTrait
{
    /**
     * @var array
     */
    protected $rawArguments = [];

    /**
     * @var ArgumentInterface[]
     */
    protected $arguments;

    /**
     * @return string
     */
    abstract public function getName(): string;

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
        if (!isAssocArray($rawArguments)) {
            throw new InvariantException(\sprintf(
                '%s.%s args must be an object with argument names as keys.',
                $typeName,
                $this->getName()
            ));
        }

        $arguments = [];

        foreach ($rawArguments as $argumentName => $argumentConfig) {
            $argumentConfig['name'] = $argumentName;

            $arguments[] = newArgument($argumentConfig);
        }

        return $arguments;
    }

    /**
     * @param string $name
     * @return ArgumentInterface|null
     */
    public function getArgument(string $name): ?ArgumentInterface
    {
        return $this->arguments[$name] ?? null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasArgument(string $name): bool
    {
        return isset($this->arguments[$name]) || \array_key_exists($name, $this->arguments);
    }
}
