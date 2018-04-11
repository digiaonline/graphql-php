<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class VariableDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{
    use DefaultValueTrait;

    /**
     * @var VariableNode
     */
    protected $variable;

    /**
     * @var TypeNodeInterface
     */
    protected $type;

    /**
     * VariableDefinitionNode constructor.
     *
     * @param VariableNode            $variable
     * @param TypeNodeInterface       $type
     * @param ValueNodeInterface|null $defaultValue
     * @param Location|null           $location
     */
    public function __construct(
        VariableNode $variable,
        TypeNodeInterface $type,
        ?ValueNodeInterface $defaultValue,
        ?Location $location
    ) {
        parent::__construct(NodeKindEnum::VARIABLE_DEFINITION, $location);

        $this->variable     = $variable;
        $this->type         = $type;
        $this->defaultValue = $defaultValue;
    }

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

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return (string)$this->type;
    }
}
