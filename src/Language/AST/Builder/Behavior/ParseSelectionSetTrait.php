<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\DirectorInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

trait ParseSelectionSetTrait
{

    /**
     * @return DirectorInterface
     */
    abstract public function getDirector(): DirectorInterface;

    /**
     * @param array $ast
     * @return NodeInterface|null
     */
    protected function parseSelectionSet(array $ast): ?NodeInterface
    {
        return isset($ast['selectionSet']) && !empty($ast['selectionSet'])
            ? $this->getDirector()->build($ast['selectionSet'])
            : null;
    }
}
