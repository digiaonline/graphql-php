<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\Node\NameTrait;
use Digia\GraphQL\Language\AST\NodeKindEnum;
use Digia\GraphQL\Language\AST\Node\DefaultValueTrait;
use Digia\GraphQL\Language\AST\Node\DefinitionNodeInterface;
use Digia\GraphQL\Language\AST\Node\TypeNodeInterface;

class VariableDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{

    use NameTrait;
    use DefaultValueTrait;

    /**
     * @var string
     */
    protected $kind = NodeKindEnum::VARIABLE_DEFINITION;

    /**
     * @var VariableNode
     */
    protected $variable;

    /**
     * @var TypeNodeInterface
     */
    protected $type;

    /**
     * @return VariableNode
     */
    public function getVariable(): VariableNode
    {
        return $this->variable;
    }

    /**
     * @return TypeNodeInterface
     */
    public function getType(): TypeNodeInterface
    {
        return $this->type;
    }
}
