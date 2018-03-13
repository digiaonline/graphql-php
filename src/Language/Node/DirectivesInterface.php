<?php

namespace Digia\GraphQL\Language\Node;

interface DirectivesInterface
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
    public function getDirectivesAsArray(): array;

    /**
     * @param DirectiveNode[] $directives
     * @return $this
     */
    public function setDirectives(array $directives);
}
