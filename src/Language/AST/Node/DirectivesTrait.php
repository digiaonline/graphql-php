<?php

namespace Digia\GraphQL\Language\AST\Node;

trait DirectivesTrait
{

    /**
     * @var DirectiveNode[]
     */
    protected $directives;

    /**
     * @return DirectiveNode[]
     */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /**
     * @return array
     */
    public function getDirectivesAsArray(): array
    {
        // TODO: Implement this method.
        return [];
    }
}
