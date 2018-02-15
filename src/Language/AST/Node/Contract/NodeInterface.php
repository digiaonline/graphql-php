<?php

namespace Digia\GraphQL\Language\AST\Node\Contract;

interface NodeInterface
{

    /**
     * @return string
     */
    public function getKind(): string;
}
