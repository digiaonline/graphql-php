<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Util\SerializationInterface;

trait ArgumentsTrait
{

    /**
     * @var InputValueDefinitionNode[]
     */
    protected $arguments;

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
}
