<?php

namespace Digia\GraphQL\Language\Node;

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
    public function getArgumentsAST(): array
    {
        return \array_map(function (InputValueDefinitionNode $node) {
            return $node->toAST();
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
