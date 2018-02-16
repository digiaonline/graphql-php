<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\NodeFactoryInterface;
use Digia\GraphQL\Language\AST\Node\DirectiveNode;

trait ParseDirectivesTrait
{

    /**
     * @return NodeFactoryInterface
     */
    abstract protected function getFactory(): NodeFactoryInterface;

    /**
     * @param array $ast
     * @return array|DirectiveNode[]
     */
    protected function parseDirectives(array $ast): array
    {
        $directives = [];

        if (isset($ast['directives'])) {
            foreach ($ast['directives'] as $directiveAst) {
                $directives[] = $this->getFactory()->build($directiveAst);
            }
        }

        return $directives;
    }
}
