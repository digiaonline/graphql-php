<?php

namespace Digia\GraphQL\Language\AST\Node;

use Digia\GraphQL\Language\AST\NodeKindEnum;

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

    /**
     * @param VariableNode $variable
     * @return VariableDefinitionNode
     */
    public function setVariable(VariableNode $variable): VariableDefinitionNode
    {
        $this->variable = $variable;
        return $this;
    }

    /**
     * @param TypeNodeInterface $type
     * @return VariableDefinitionNode
     */
    public function setType(TypeNodeInterface $type): VariableDefinitionNode
    {
        $this->type = $type;
        return $this;
    }
}
