<?php

namespace Digia\GraphQL\Language\Writer;

use Digia\GraphQL\Language\Node\NodeInterface;
use Digia\GraphQL\Language\Node\VariableDefinitionNode;
use function Digia\GraphQL\Language\wrap;

class VariableDefinitionWriter extends AbstractWriter
{
    /**
     * @param NodeInterface|VariableDefinitionNode $node
     * @inheritdoc
     */
    public function write(NodeInterface $node): string
    {
        $variable     = $this->printNode($node->getVariable());
        $type         = $this->printNode($node->getType());
        $defaultValue = $this->printNode($node->getDefaultValue());

        return $variable . ': ' . $type . wrap(' = ', $defaultValue);
    }

    /**
     * @inheritdoc
     */
    public function supportsWriter(NodeInterface $node): bool
    {
        return $node instanceof VariableDefinitionNode;
    }
}
