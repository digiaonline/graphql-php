<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Language\AST\Node\DirectiveNode;

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
}
