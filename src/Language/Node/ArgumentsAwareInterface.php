<?php

namespace Digia\GraphQL\Language\Node;

interface ArgumentsAwareInterface
{
    /**
     * @return bool
     */
    public function hasArguments(): bool;

    /**
     * @return ArgumentNode[]
     */
    public function getArguments(): array;

    /**
     * @return array
     */
    public function getArgumentsAST(): array;

    /**
     * @param ArgumentNode[] $arguments
     * @return $this
     */
    public function setArguments(array $arguments);
}
