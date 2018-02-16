<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\NodeFactoryInterface;
use Digia\GraphQL\Language\AST\Node\ArgumentNode;

trait ParseArgumentsTrait
{

    /**
     * @return NodeFactoryInterface
     */
    abstract protected function getFactory(): NodeFactoryInterface;

    /**
     * @param array $ast
     * @return array|ArgumentNode[]
     */
    protected function parseArguments(array $ast): array
    {
        $arguments = [];

        if (isset($ast['arguments'])) {
            foreach ($ast['arguments'] as $argumentAst) {
                $arguments[] = $this->getFactory()->build($argumentAst);
            }
        }

        return $arguments;
    }
}
