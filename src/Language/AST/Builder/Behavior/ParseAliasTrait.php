<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\DirectorInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

trait ParseAliasTrait
{

    /**
     * @return DirectorInterface
     */
    abstract protected function getDirector(): DirectorInterface;

    /**
     * @param array $ast
     * @return NodeInterface|null
     */
    protected function parseAlias(array $ast): ?NodeInterface
    {
        return isset($ast['alias']) ? $this->getDirector()->build($ast['alias']) : null;
    }
}
