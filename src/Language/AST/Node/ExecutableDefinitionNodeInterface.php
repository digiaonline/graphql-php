<?php

namespace Digia\GraphQL\Language\AST\Node;

interface ExecutableDefinitionNodeInterface extends DefinitionNodeInterface
{
    /**
     * @return null|string
     */
    public function getNameValue(): ?string;
}
