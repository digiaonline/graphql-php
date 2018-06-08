<?php

namespace Digia\GraphQL\Language\Node;

interface DirectivesAwareInterface
{
    /**
     * @return bool
     */
    public function hasDirectives(): bool;

    /**
     * @return DirectiveNode[]
     */
    public function getDirectives(): array;

    /**
     * @return array
     */
    public function getDirectivesAST(): array;

    /**
     * @param DirectiveNode[] $directives
     * @return $this
     */
    public function setDirectives(array $directives);
}
