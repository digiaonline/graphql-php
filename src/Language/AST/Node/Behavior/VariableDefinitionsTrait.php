<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

use Digia\GraphQL\Contract\SerializationInterface;
use Digia\GraphQL\Language\AST\Node\VariableDefinitionNode;

trait VariableDefinitionsTrait
{

    /**
     * @var VariableDefinitionNode[]
     */
    protected $variableDefinitions;

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
    public function getVariableDefinitionsAsArray(): array
    {
        return array_map(function (SerializationInterface $node) {
            return $node->toArray();
        }, $this->variableDefinitions);
    }
}
