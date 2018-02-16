<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\NodeFactoryInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

trait ParseAliasTrait
{

    /**
     * @return NodeFactoryInterface
     */
    abstract protected function getFactory(): NodeFactoryInterface;

    /**
     * @param array $ast
     * @return NodeInterface|null
     */
    protected function parseAlias(array $ast): ?NodeInterface
    {
        return isset($ast['alias']) ? $this->getFactory()->build($ast['alias']) : null;
    }
}
