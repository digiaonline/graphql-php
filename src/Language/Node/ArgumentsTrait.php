<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Util\SerializationInterface;

trait ArgumentsTrait
{
    /**
     * @var ArgumentNode[]
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
     * @return ArgumentNode[]
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
        return \array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->getArguments());
    }

    /**
     * @param ArgumentNode[] $arguments
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }
}
