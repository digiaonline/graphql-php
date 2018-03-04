<?php

namespace Digia\GraphQL\Language\AST\Node;

interface TypeDefinitionNodeInterface extends DefinitionNodeInterface
{

    /**
     * @return NameNode|null
     */
    public function getName(): ?NameNode;

    /**
     * @return null|string
     */
    public function getNameValue(): ?string;
}
