<?php

namespace Digia\GraphQL\Language\Node;

interface ExecutableDefinitionNodeInterface extends DefinitionNodeInterface
{
    /**
     * @return null|string
     */
    public function getNameValue(): ?string;
}
