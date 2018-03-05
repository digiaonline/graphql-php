<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Util\SerializationInterface;

trait InputArgumentsTrait
{

    /**
     * @var InputValueDefinitionNode[]
     */
    protected $arguments;

    /**
     * @return bool
     */
    public function hasArguments(): bool
    {
        return !empty($this->arguments);
    }

    /**
     * @return InputValueDefinitionNode[]
     */
    public function getArguments(): array
    {
        return $this->arguments ?? [];
    }

    /**
     * @return array
     */
    public function getArgumentsAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->arguments);
    }

    /**
     * @param array|InputValueDefinitionNode[] $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }
}
