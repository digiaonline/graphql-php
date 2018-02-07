<?php

namespace Digia\GraphQL\Language\AST\Node;

interface NodeInterface
{

    /**
     * @return null|string
     */
    public function getKind(): ?string;

    /**
     * @return mixed
     */
    public function getValue();
}
