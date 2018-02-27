<?php

namespace Digia\GraphQL\Language\AST\Node\Contract;

interface DefinitionNodeInterface extends NodeInterface
{

    /**
     * @return string
     */
    function getKind(): string;
}
