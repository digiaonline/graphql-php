<?php

namespace Digia\GraphQL\Language\Node;

use Digia\GraphQL\Language\Location;

class VariableDefinitionNode extends AbstractNode implements DefinitionNodeInterface
{
    use DefaultValueTrait;
    use TypeTrait;

    /**
     * @var VariableNode
     */
    protected $variable;

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
     * @return array
     */
    public function getVariableAST(): array
    {
        return $this->variable->toAST();
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return (string)$this->type;
    }

    /**
     * @inheritdoc
     */
    public function toAST(): array
    {
        return [
            'kind'     => $this->kind,
            'variable' => $this->getVariableAST(),
            'type'     => $this->getTypeAST(),
            'loc'      => $this->getLocationAST(),
        ];
    }
}
