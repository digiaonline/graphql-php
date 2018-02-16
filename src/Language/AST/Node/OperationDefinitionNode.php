<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\ASTNodeInterface;
use Digia\GraphQL\Language\AST\KindEnum;

/**
 * Class OperationDefinitionNode
 * @package Digia\GraphQL\Language\AST\Node
 */
class OperationDefinitionNode implements ASTNodeInterface
{

    /**
     * @return string
     */
    public function getKind(): string
    {
        return KindEnum::OPERATION_DEFINITION;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        // TODO: Implement getValue() method.
    }
}
