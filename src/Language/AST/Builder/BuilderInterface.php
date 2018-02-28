<?php

namespace Digia\GraphQL\Language\AST\Builder;

use Digia\GraphQL\Language\AST\Node\NodeInterface;

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
