<?php

namespace Digia\GraphQL\Language\Node;

trait VariableDefinitionsTrait
{
    /**
     * @var VariableDefinitionNode[]
     */
    protected $variableDefinitions = [];

    /**
     * @return VariableDefinitionNode[]
     */
    public function getVariableDefinitions(): array
    {
        return $this->variableDefinitions;
    }

    /**
     * @return array
     */
    public function getVariableDefinitionsAST(): array
    {
        return \array_map(function (VariableDefinitionNode $node) {
            return $node->toAST();
        }, $this->variableDefinitions);
    }

    /**
     * @param VariableDefinitionNode[] $variableDefinitions
     * @return $this
     */
    protected function setVariableDefinitions(array $variableDefinitions)
    {
        $this->variableDefinitions = $variableDefinitions;
        return $this;
    }
}
