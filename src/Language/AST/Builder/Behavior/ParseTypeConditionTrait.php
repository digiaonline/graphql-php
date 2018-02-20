<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\DirectorInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

trait ParseTypeConditionTrait
{

    /**
     * @return DirectorInterface
     */
    abstract public function getDirector(): DirectorInterface;

    /**
     * @param array $ast
     * @return NodeInterface
     */
    protected function parseTypeCondition(array $ast): NodeInterface
    {
        return isset($ast['typeCondition']) ? $this->getDirector()->build($ast['typeCondition']) : null;
    }
}
