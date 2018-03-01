<?php

namespace Digia\GraphQL\Language\AST\Node;

interface DefinitionNodeInterface extends NodeInterface
{

    /**
     * @return string
     */
    function getKind(): string;
}
