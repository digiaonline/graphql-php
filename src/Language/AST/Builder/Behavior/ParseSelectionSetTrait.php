<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\NodeFactoryInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

trait ParseSelectionSetTrait
{

    /**
     * @return NodeFactoryInterface
     */
    abstract protected function getFactory(): NodeFactoryInterface;

    /**
     * @param array $ast
     * @return NodeInterface|null
     */
    protected function parseSelectionSet(array $ast): ?NodeInterface
    {
        return isset($ast['selectionSet']) ? $this->getFactory()->build($ast['selectionSet']) : null;
    }
}
