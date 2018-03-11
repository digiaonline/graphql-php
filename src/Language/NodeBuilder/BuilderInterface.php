<?php

namespace Digia\GraphQL\Language\NodeBuilder;

use Digia\GraphQL\Language\Node\NodeInterface;

interface BuilderInterface
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
    public function supportsKind(string $kind): bool;

    /**
     * @param DirectorInterface $factory
     */
    public function setDirector(DirectorInterface $factory);
}
