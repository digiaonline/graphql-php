<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\DirectorInterface;
use Digia\GraphQL\Language\AST\Node\Contract\NodeInterface;

trait ParseValueLiteralTrait
{

    /**
     * @return DirectorInterface
     */
    abstract public function getDirector(): DirectorInterface;

    /**
     * @param array  $ast
     * @param string $fieldName
     * @return NodeInterface|null
     */
    protected function parseValueLiteral(array $ast, string $fieldName = 'value'): ?NodeInterface
    {
        return isset($ast[$fieldName]) ? $this->getDirector()->build($ast[$fieldName]) : null;
    }
}
