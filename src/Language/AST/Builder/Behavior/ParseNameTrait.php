<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\NodeFactoryInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

trait ParseNameTrait
{

    /**
     * @return NodeFactoryInterface
     */
    abstract protected function getFactory(): NodeFactoryInterface;

    /**
     * @param array $ast
     * @return NodeInterface|null
     */
    protected function parseName(array $ast): ?NodeInterface
    {
        return isset($ast['name']) ? $this->getFactory()->build($ast['name']) : null;
    }
}
