<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\ASTNodeInterface;
use Digia\GraphQL\Language\AST\KindEnum;

class DocumentNode implements ASTNodeInterface
{

    /**
     * @return string
     */
    public function getKind(): string
    {
        return KindEnum::DOCUMENT;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        // TODO: Implement getValue() method.
    }
}
