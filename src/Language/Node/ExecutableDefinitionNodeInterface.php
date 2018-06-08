<?php

namespace Digia\GraphQL\Language\Node;

interface ExecutableDefinitionNodeInterface extends DefinitionNodeInterface
{
    // TODO: Figure out of the `getNameValue` method can be moved to `DefinitionNodeInterface`.

    /**
     * @return null|string
     */
    public function getNameValue(): ?string;
}
