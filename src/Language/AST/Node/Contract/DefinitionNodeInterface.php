<?php

namespace Digia\GraphQL\Language\AST\Node\Contract;

use Digia\GraphQL\Language\AST\Node\NameNode;

interface DefinitionNodeInterface extends NodeInterface
{
    /**
     * @return string
     */
    function getKind(): string;

    /**
     * @return NameNode
     */
    function getName(): NameNode;
}
