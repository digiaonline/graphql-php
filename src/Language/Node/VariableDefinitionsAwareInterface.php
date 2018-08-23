<?php

namespace Digia\GraphQL\Language\Node;

interface VariableDefinitionsAwareInterface
{
    /**
     * @return VariableDefinitionNode[]
     */
    public function getVariableDefinitions(): array;
}
