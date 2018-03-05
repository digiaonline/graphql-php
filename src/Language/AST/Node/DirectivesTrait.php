<?php

namespace Digia\GraphQL\Language\AST\Node;

trait DirectivesTrait
{

    /**
     * @var DirectiveNode[]
     */
    protected $directives;

    /**
     * @return bool
     */
    public function hasDirectives(): bool
    {
        return !empty($this->directives);
    }

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

    /**
     * @param array|DirectiveNode[] $directives
     * @return $this
     */
    public function setDirectives(array $directives)
    {
        $this->directives = $directives;
        return $this;
    }
}
