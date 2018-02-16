<?php

namespace Digia\GraphQL\Language\AST\Node\Behavior;

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
}
