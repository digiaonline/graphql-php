<?php

namespace Digia\GraphQL\Language\AST;

interface ASTNodeInterface
{

    /**
     * @return string
     */
    public function getKind(): string;

    /**
     * @return mixed
     */
    public function getValue();
}
