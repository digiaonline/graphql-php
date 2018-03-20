<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;

interface NodeBuilderInterface
{
    /**
     * @param array $ast
     * @return NodeInterface
     */
    public function build(array $ast): NodeInterface;

    /**
     * @param string $kind
     * @return bool
     */
    public function supportsBuilder(string $kind): bool;

    /**
     * @param NodeDirectorInterface $factory
     */
    public function setDirector(NodeDirectorInterface $factory);
}
