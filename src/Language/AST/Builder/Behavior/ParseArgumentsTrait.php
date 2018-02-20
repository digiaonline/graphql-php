<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\DirectorInterface;
use Digia\GraphQL\Language\AST\Node\ArgumentNode;

trait ParseArgumentsTrait
{

    /**
     * @return DirectorInterface
     */
    abstract protected function getDirector(): DirectorInterface;

    /**
     * @param array $ast
     * @return array|ArgumentNode[]
     */
    protected function parseArguments(array $ast): array
    {
        $arguments = [];

        if (isset($ast['arguments'])) {
            foreach ($ast['arguments'] as $argumentAst) {
                $arguments[] = $this->getDirector()->build($argumentAst);
            }
        }

        return $arguments;
    }
}
