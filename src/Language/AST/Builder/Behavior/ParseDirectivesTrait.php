<?php

namespace Digia\GraphQL\Language\AST\Builder\Behavior;

use Digia\GraphQL\Language\AST\Builder\Contract\DirectorInterface;
use Digia\GraphQL\Language\AST\Node\DirectiveNode;

trait ParseDirectivesTrait
{

    /**
     * @return DirectorInterface
     */
    abstract public function getDirector(): DirectorInterface;

    /**
     * @param array $ast
     * @return array|DirectiveNode[]
     */
    protected function parseDirectives(array $ast): array
    {
        $directives = [];

        if (isset($ast['directives'])) {
            foreach ($ast['directives'] as $directiveAst) {
                $directives[] = $this->getDirector()->build($directiveAst);
            }
        }

        return $directives;
    }
}
